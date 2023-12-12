WITH RECURSIVE month_offset AS (
    SELECT 
        0 AS month_offset,
        CURDATE() AS corresponding_date,
        DATE_FORMAT(CURDATE(), '%M %Y') AS formatted_date
    UNION ALL
    SELECT 
        month_offset + 1,
        DATE_ADD(CURDATE(), INTERVAL (month_offset + 1) MONTH) AS corresponding_date,
        DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL (month_offset + 1) MONTH), '%M %Y') AS formatted_date
    FROM month_offset
    WHERE month_offset < 500
)

,units AS (
	select distinct
		id
		,project
		,model
		,unit_number as unit
		,price
		,bedrooms
		,bathrooms
		,den
		,interior_size
	from condo_app.pre_con_unit_database_20230827_v4        
	where true
		-- and project = '75_James'
)

FROM NumberSeries;


,deposits_staging as (
	SELECT
		pc.id
		,pc.project
        ,pc.model
        ,pc.unit
        ,pc.price
        ,deposit_date_t_plus_today
        ,deposit_date
        ,deposit_percent
        ,deposit_dollar
        ,round(deposit_date_t_plus_today / 30) as deposit_date_t_plus_today_months
        ,TIMESTAMPDIFF(MONTH, current_date, deposit_date)
        ,round(deposit_date_t_plus_today / 30) + coalesce(timestampdiff(MONTH, current_date, deposit_date),0) as month_offset
		,price * deposit_percent + deposit_dollar as deposit
        ,case when deposit_occupancy = 'TRUE' then price * land_transfer_tax else 0 end as closing_costs
	FROM units pc
		LEFT JOIN condo_app.deposit_structure dp ON dp.project = pc.project
		cross JOIN condo_app.default_values
	where true
		-- and pc.project = '75_James'
	ORDER BY pc.project, model, price, cast(deposit_num as signed)
)

,deposits as (
	select
		id
		,project
        ,model
        ,unit
		,month_offset
        ,sum(deposit) as deposit
        ,sum(closing_costs) as closing_costs
	from deposits_staging
    group by 1,2,3,4,5
)

,mortgage as (
	SELECT distinct
		pc.id
        ,pc.project
		,pc.model
		,pc.unit
        ,pc.price
		,round(deposit_date_t_plus_today / 30) + coalesce(timestampdiff(MONTH, current_date, deposit_date),0) as month_offset
		,deposit_num
	    ,pc.price * (1 - downpayment_percent) as present_value
	    ,(mortgage_rate/mortgage_payments_year) as rate
	    ,mortgage_amortization_years * mortgage_payments_year as number_of_periods
		,case when deposit_occupancy = 'TRUE' then round((price * (1-downpayment_percent) * (mortgage_rate/mortgage_payments_year) * POW(1 + (mortgage_rate/mortgage_payments_year), mortgage_amortization_years*mortgage_payments_year)) / (POW(1 + (mortgage_rate/mortgage_payments_year), mortgage_amortization_years*mortgage_payments_year) - 1)) else null end as mortgage_payment
	FROM units pc
		LEFT JOIN condo_app.deposit_structure dp ON dp.project = pc.project
		cross JOIN condo_app.default_values
	where true
		and deposit_occupancy = 'TRUE'
-- 		and pc.project in ('75_James','8_Elm')
        -- and pc.unit in ('913','2308','613')
	-- 	and pc.model in ('1D-07','1D+D','S-03T','1K+M')
)

,rents as (
	select
		pc.*
		,rd.price as rent_raw
        ,CAST(REPLACE(REPLACE(rd.price, '$', ''), ',', '') AS SIGNED) as rent
		,rd.bedrooms_clean
		,rd.sqft_range
		,rd.sqft_min
		,rd.sqft_max
		,rd.sqft_midpoint
		,ATAN2(SQRT(POW(COS(RADIANS(rd.lat)) * SIN(RADIANS(pll.lon - rd.lon)),2) + POW(COS(RADIANS(pll.lat)) * SIN(RADIANS(rd.lat)) - SIN(RADIANS(pll.lat)) * COS(RADIANS(rd.lat)) * COS(RADIANS(pll.lon - rd.lon)),2)),(SIN(RADIANS(pll.lat)) * SIN(RADIANS(rd.lat)) + COS(RADIANS(pll.lat)) * COS(RADIANS(rd.lat)) * COS(RADIANS(pll.lon - rd.lon)))) * 6372.795 as haversine
		,row_number() over(partition by pc.project, model, unit order by ATAN2(SQRT(POW(COS(RADIANS(rd.lat)) * SIN(RADIANS(pll.lon - rd.lon)),2) + POW(COS(RADIANS(pll.lat)) * SIN(RADIANS(rd.lat)) - SIN(RADIANS(pll.lat)) * COS(RADIANS(rd.lat)) * COS(RADIANS(pll.lon - rd.lon)),2)),(SIN(RADIANS(pll.lat)) * SIN(RADIANS(rd.lat)) + COS(RADIANS(pll.lat)) * COS(RADIANS(rd.lat)) * COS(RADIANS(pll.lon - rd.lon)))) * 6372.795) as proximity_rank
	from units pc
		left join condo_app.pre_con_latlon_20230827 pll on pll.project = pc.project
		join condo_app.rental_data_20231016 rd on rd.bedrooms_clean = pc.bedrooms
	where true
		-- and pc.project = '75_James'
-- 		and unit = '913'
	order by pc.project, model, unit, haversine
)

,rents_agg as (
	select
		r.id
		,r.project
		,model
		,unit
		,month_offset
		,r.price
		,maintenance_per_sqft 
		,r.interior_size
		,property_taxes_percent
		,maintenance_per_sqft * interior_size
		,price * property_taxes_percent 
		,maintenance_per_sqft * r.interior_size + r.price * property_taxes_percent / 12 as rent_expenses
	    ,pow((1 + rental_appreciation_percent_yearly),1.0/12) - 1 as rental_appreciation_percent_monthly
		,avg(rent) as rent
	    -- ,2500 as rent
	from rents r
		left join (select distinct project, month_offset from mortgage) pm on pm.project = r.project
		cross join condo_app.default_values dv
	where true
		and proximity_rank <= 10
	group by 1,2,3,4,5,6,7,8,9,10,11,12
)

,cashflows as (
	SELECT
		upm.id
		,upm.project
		,upm.model
		,upm.unit
		,upm.price
		,mo.month_offset
		,m.month_offset as mortgage_month_offset
		,mo.corresponding_date
		,mo.formatted_date
		,COALESCE(d.deposit, 0) AS deposit
		,COALESCE(d.closing_costs, 0) AS closing_costs
	-- 	from mortage table
		,coalesce(mo.month_offset - m.month_offset + 1,0) as month_since_occupancy
		,coalesce(case when mo.month_offset < m.month_offset then 0 else m.mortgage_payment end,0) AS mortgage_payment
		,coalesce(case when mo.month_offset < m.month_offset then 0 else round(((rate * present_value) / (1 - POW(1 + rate, -number_of_periods))) - ((present_value * POW(1 + rate, (mo.month_offset - m.month_offset + 1) - 1)) - ((rate * present_value) / (1 - POW(1 + rate, -number_of_periods))) * (POW(1 + rate, (mo.month_offset - m.month_offset + 1) - 1) - 1) / rate) * rate) end,0) AS mortgage_principal
		,coalesce(case when mo.month_offset < m.month_offset then 0 else mortgage_payment - round(((rate * present_value) / (1 - POW(1 + rate, -number_of_periods))) - ((present_value * POW(1 + rate, (mo.month_offset - m.month_offset + 1) - 1)) - ((rate * present_value) / (1 - POW(1 + rate, -number_of_periods))) * (POW(1 + rate, (mo.month_offset - m.month_offset + 1) - 1) - 1) / rate) * rate) end,0) as mortgage_interest
	-- 	from rent table
		,coalesce(round(rent * pow(1 + rental_appreciation_percent_monthly,mo.month_offset)),0) as rent
		,coalesce(round(rent_expenses / rent * round(rent * pow(1 + rental_appreciation_percent_monthly,mo.month_offset))),0) as rent_expenses
		,coalesce(round(rent * pow(1 + rental_appreciation_percent_monthly,mo.month_offset)) - round(rent_expenses / rent * round(rent * pow(1 + rental_appreciation_percent_monthly,mo.month_offset))),0) as rental_net_income
	FROM month_offset mo
		CROSS JOIN (SELECT distinct id, project ,model, unit, price from units) upm
		LEFT JOIN mortgage m on m.month_offset <= mo.month_offset and m.id = upm.id -- and m.project = upm.project and m.model = upm.model and m.unit = upm.unit
		left join deposits d on mo.month_offset = d.month_offset and d.id = upm.id -- and d.project = upm.project and d.model = upm.model and d.unit = upm.unit
		left join rents_agg r on mo.month_offset >= r.month_offset and r.id = upm.id -- and r.project = upm.project and r.model = upm.model and r.unit = upm.unit
 --    where true
		-- and upm.project in ('Olive_Residences','Union_City')
        -- and upm.model in ('Emerald','PENTHOUSE SUITE 10','PENTHOUSE SUITE 01')
	-- group by 1,2,3,4,5,6
	order by upm.id, upm.project, upm.model, upm.unit, mo.month_offset
)

-- select
-- 	upm.id
-- 	,upm.project
-- 	,upm.model
-- 	,upm.unit
-- 	,upm.price
--     ,mortgage_month_offset
--     ,count(*) as num_instances
--     ,sum(case when mortgage_month_offset is not null then 1 else 0 end) as num_mortgage_month_offset
--     ,sum(case when mortgage_month_offset is not null then 1 else 0 end) + mortgage_month_offset as should_be_501
-- from cashflows upm
-- group by 1,2,3,4,5,6
-- having mortgage_month_offset is not null

-- INSERT INTO condo_app.cashflows_20231120_v1 (
--     id,
--     project,
--     model,
--     unit,
--     mortgage_rate,
--     mortgage_payments_year,
--     mortgage_amortization_years,
--     downpayment_percent,
--     rental_appreciation_percent_yearly,
--     maintenance_per_sqft,
--     maintenance_appreciation_monthly,
--     property_taxes_percent,
--     selling_costs,
--     land_transfer_tax,
--     occupancy_index,
--     months_from_today,
--     corresponding_date,
--     formatted_date,
--     deposits,
--     closing_costs,
--     months_from_occupancy,
--     mortgage_payment,
--     mortgage_principal,
--     mortgage_interest,
--     rent,
--     rent_expenses,
--     rental_net_income
-- )

-- CREATE TABLE condo_app.cashflows_20231120_v2 (
--     id INT PRIMARY KEY,
--     project VARCHAR(255),
--     model VARCHAR(255),
--     unit VARCHAR(255),
--     mortgage_rate DECIMAL(10, 2),
--     mortgage_payments_year INT,
--     mortgage_amortization_years INT,
--     downpayment_percent DECIMAL(5, 2),
--     rental_appreciation_percent_yearly DECIMAL(5, 2),
--     maintenance_per_sqft DECIMAL(10, 2),
--     maintenance_appreciation_monthly DECIMAL(10, 2),
--     property_taxes_percent DECIMAL(5, 2),
--     selling_costs DECIMAL(10, 2),
--     land_transfer_tax DECIMAL(10, 2),
--     occupancy_index INT,
--     months_from_today TEXT,
--     corresponding_date TEXT,
--     formatted_date TEXT,
--     deposits TEXT,
--     closing_costs TEXT,
--     months_from_occupancy TEXT,
--     mortgage_payment TEXT,
--     mortgage_principal TEXT,
--     mortgage_interest TEXT,
--     rent TEXT,
--     rent_expenses TEXT,
--     rental_net_income TEXT
--     -- updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

SELECT 
    c.id,
    c.project,
    c.model,
    c.unit,
    mortgage_rate,
    mortgage_payments_year,
    mortgage_amortization_years,
    downpayment_percent,
    rental_appreciation_percent_yearly,
    maintenance_per_sqft,
    maintenance_appreciation_monthly,
    property_taxes_percent,
    selling_costs,
    land_transfer_tax,
    occupancy_index,
    CONCAT('[', GROUP_CONCAT(month_offset ORDER BY month_offset), ']') AS months_from_today,
	CONCAT('[', GROUP_CONCAT('\"', DATE_FORMAT(corresponding_date, '%Y-%m-%d'), '\"' ORDER BY month_offset), ']') AS corresponding_date,
	CONCAT('[', GROUP_CONCAT('\"', formatted_date, '\"' ORDER BY month_offset), ']') AS formatted_date,
    CONCAT('[', GROUP_CONCAT(deposit ORDER BY month_offset), ']') AS deposits,
    CONCAT('[', GROUP_CONCAT(closing_costs ORDER BY month_offset), ']') AS closing_costs,
    CONCAT('[', GROUP_CONCAT(month_since_occupancy ORDER BY month_offset), ']') AS months_from_occupancy,
    CONCAT('[', GROUP_CONCAT(mortgage_payment ORDER BY month_offset), ']') AS mortgage_payment,
    CONCAT('[', GROUP_CONCAT(mortgage_principal ORDER BY month_offset), ']') AS mortgage_principal,
    CONCAT('[', GROUP_CONCAT(mortgage_interest ORDER BY month_offset), ']') AS mortgage_interest,
    CONCAT('[', GROUP_CONCAT(rent ORDER BY month_offset), ']') AS rent,
    CONCAT('[', GROUP_CONCAT(rent_expenses ORDER BY month_offset), ']') AS rent_expenses,
    CONCAT('[', GROUP_CONCAT(rental_net_income ORDER BY month_offset), ']') AS rental_net_income
FROM cashflows c
    LEFT JOIN (SELECT id, project, model, unit, month_offset AS occupancy_index FROM mortgage) oi ON c.project = oi.project AND c.model = oi.model AND c.unit = oi.unit
    CROSS JOIN condo_app.default_values dv
WHERE c.project != 'Seaton'
GROUP BY 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15;