-- Demo records for the supervisor workspace.
-- Safe to run more than once: every row is identified by its DEMO-ALLAN marker.
USE farmersmkt_db;

SET @allan_id = (SELECT user_id FROM users WHERE user_name = 'Nahereza Allan' AND role = 5 AND status = 1 ORDER BY user_id LIMIT 1);
SET @allan_name = (SELECT user_name FROM users WHERE user_id = @allan_id);
SET @farm_id = (SELECT farm_id FROM farmer WHERE status = 1 ORDER BY farm_id LIMIT 1);

INSERT INTO farm_visits (farm_id, supervisor_id, visit_date, status, notes)
SELECT @farm_id, @allan_id, visit_date, visit_status, visit_notes
FROM (
    SELECT DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS visit_date, 'Completed' AS visit_status, '[DEMO-ALLAN-V01] Checked irrigation lines and crop health.' AS visit_notes
    UNION ALL SELECT DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Completed', '[DEMO-ALLAN-V02] Reviewed harvest readiness with the farm team.'
    UNION ALL SELECT DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Completed', '[DEMO-ALLAN-V03] Verified storage area and produce handling practices.'
    UNION ALL SELECT DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Completed', '[DEMO-ALLAN-V04] Recorded soil preparation findings and follow-up actions.'
    UNION ALL SELECT DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Completed', '[DEMO-ALLAN-V05] Checked water access and documented maintenance needs.'
    UNION ALL SELECT DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Scheduled', '[DEMO-ALLAN-V06] Follow up on seedling progress.'
    UNION ALL SELECT DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Scheduled', '[DEMO-ALLAN-V07] Inspect pest-control measures.'
    UNION ALL SELECT DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Scheduled', '[DEMO-ALLAN-V08] Review next harvest collection plan.'
    UNION ALL SELECT DATE_ADD(CURDATE(), INTERVAL 4 DAY), 'Scheduled', '[DEMO-ALLAN-V09] Confirm market delivery quantities.'
    UNION ALL SELECT DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Scheduled', '[DEMO-ALLAN-V10] Complete seasonal production check.'
) AS demo_visits
WHERE @allan_id IS NOT NULL AND @farm_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM farm_visits WHERE supervisor_id = @allan_id AND notes = demo_visits.visit_notes);

INSERT INTO supervisor_activity_log (actor_id, actor_name, action_type, target_type, target_id, notes)
SELECT @allan_id, @allan_name, action_type, target_type, target_id, activity_notes
FROM (
    SELECT 'visit_completed' AS action_type, 'farm_visit' AS target_type, 1 AS target_id, '[DEMO-ALLAN-A01] Completed routine farm inspection.' AS activity_notes
    UNION ALL SELECT 'visit_completed', 'farm_visit', 2, '[DEMO-ALLAN-A02] Confirmed crop health findings.'
    UNION ALL SELECT 'visit_completed', 'farm_visit', 3, '[DEMO-ALLAN-A03] Updated storage and handling notes.'
    UNION ALL SELECT 'visit_completed', 'farm_visit', 4, '[DEMO-ALLAN-A04] Logged soil preparation follow-up.'
    UNION ALL SELECT 'visit_completed', 'farm_visit', 5, '[DEMO-ALLAN-A05] Recorded water access observations.'
    UNION ALL SELECT 'visit_scheduled', 'farm_visit', 6, '[DEMO-ALLAN-A06] Scheduled seedling progress review.'
    UNION ALL SELECT 'visit_scheduled', 'farm_visit', 7, '[DEMO-ALLAN-A07] Scheduled pest-control inspection.'
    UNION ALL SELECT 'visit_scheduled', 'farm_visit', 8, '[DEMO-ALLAN-A08] Scheduled harvest plan review.'
    UNION ALL SELECT 'visit_scheduled', 'farm_visit', 9, '[DEMO-ALLAN-A09] Scheduled delivery quantity check.'
    UNION ALL SELECT 'visit_scheduled', 'farm_visit', 10, '[DEMO-ALLAN-A10] Scheduled seasonal production check.'
) AS demo_activity
WHERE @allan_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM supervisor_activity_log WHERE actor_id = @allan_id AND notes = demo_activity.activity_notes);

INSERT INTO supervisor_reports (supervisor_id, supervisor_name, title, report_body)
SELECT @allan_id, @allan_name, report_title, report_details
FROM (
    SELECT '[DEMO-ALLAN-R01] Weekly irrigation review' AS report_title, 'Irrigation lines were checked and the main maintenance item was recorded for follow-up.' AS report_details
    UNION ALL SELECT '[DEMO-ALLAN-R02] Harvest readiness update', 'The inspected plots are progressing well and the next harvest window was noted.'
    UNION ALL SELECT '[DEMO-ALLAN-R03] Produce handling check', 'Storage and handling practices were reviewed with the farm team.'
    UNION ALL SELECT '[DEMO-ALLAN-R04] Soil preparation findings', 'Soil preparation is on track; the team should complete the listed follow-up task.'
    UNION ALL SELECT '[DEMO-ALLAN-R05] Water access report', 'Water access remains available and one maintenance item needs attention.'
    UNION ALL SELECT '[DEMO-ALLAN-R06] Seedling progress report', 'Seedlings are developing consistently and should be reviewed again during the next visit.'
    UNION ALL SELECT '[DEMO-ALLAN-R07] Pest-control observation', 'Preventive pest-control measures were observed and the next inspection was scheduled.'
    UNION ALL SELECT '[DEMO-ALLAN-R08] Collection planning note', 'Collection quantities were reviewed against the expected market demand.'
    UNION ALL SELECT '[DEMO-ALLAN-R09] Delivery coordination report', 'Delivery planning is progressing and the farm team has been briefed on quantities.'
    UNION ALL SELECT '[DEMO-ALLAN-R10] Seasonal operations summary', 'Seasonal production checks are complete for this demo reporting period.'
) AS demo_reports
WHERE @allan_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM supervisor_reports WHERE supervisor_id = @allan_id AND title = demo_reports.report_title);

INSERT INTO extra_cost_requests (requested_by, requested_by_name, cost_name, amount, reason, status)
SELECT @allan_id, @allan_name, cost_name, amount, reason, 'pending'
FROM (
    SELECT '[DEMO-ALLAN-C01] Irrigation inspection' AS cost_name, 85000.00 AS amount, 'Transport and inspection supplies for the irrigation review.' AS reason
    UNION ALL SELECT '[DEMO-ALLAN-C02] Harvest assessment', 120000.00, 'Field transport and measurement supplies for harvest readiness checks.'
    UNION ALL SELECT '[DEMO-ALLAN-C03] Storage audit', 95000.00, 'Materials needed for the produce storage and handling audit.'
    UNION ALL SELECT '[DEMO-ALLAN-C04] Soil testing visit', 150000.00, 'Basic soil testing and field follow-up for the assigned farm.'
    UNION ALL SELECT '[DEMO-ALLAN-C05] Water access follow-up', 110000.00, 'Transport and minor inspection materials for the water access review.'
    UNION ALL SELECT '[DEMO-ALLAN-C06] Seedling review', 90000.00, 'Field transport for the scheduled seedling progress review.'
    UNION ALL SELECT '[DEMO-ALLAN-C07] Pest-control inspection', 135000.00, 'Inspection materials and transport for pest-control verification.'
    UNION ALL SELECT '[DEMO-ALLAN-C08] Collection planning', 100000.00, 'Planning materials for the next produce collection cycle.'
    UNION ALL SELECT '[DEMO-ALLAN-C09] Delivery coordination', 125000.00, 'Transport and coordination costs for delivery quantity confirmation.'
    UNION ALL SELECT '[DEMO-ALLAN-C10] Seasonal review', 175000.00, 'Field materials and transport for the seasonal production review.'
) AS demo_requests
WHERE @allan_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM extra_cost_requests WHERE requested_by = @allan_id AND cost_name = demo_requests.cost_name);

INSERT INTO extra_costs (cost_name, amount, notes, created_by)
SELECT cost_name, amount, notes, @allan_id
FROM (
    SELECT '[DEMO-ALLAN-L01] Field transport' AS cost_name, 70000.00 AS amount, 'Demo ledger entry for field transport.' AS notes
    UNION ALL SELECT '[DEMO-ALLAN-L02] Inspection supplies', 55000.00, 'Demo ledger entry for inspection supplies.'
    UNION ALL SELECT '[DEMO-ALLAN-L03] Sampling materials', 80000.00, 'Demo ledger entry for sampling materials.'
    UNION ALL SELECT '[DEMO-ALLAN-L04] Farm mapping', 60000.00, 'Demo ledger entry for farm mapping.'
    UNION ALL SELECT '[DEMO-ALLAN-L05] Safety equipment', 90000.00, 'Demo ledger entry for field safety equipment.'
    UNION ALL SELECT '[DEMO-ALLAN-L06] Seedling review transport', 65000.00, 'Demo ledger entry for seedling review transport.'
    UNION ALL SELECT '[DEMO-ALLAN-L07] Pest-control materials', 105000.00, 'Demo ledger entry for pest-control materials.'
    UNION ALL SELECT '[DEMO-ALLAN-L08] Collection planning', 75000.00, 'Demo ledger entry for collection planning.'
    UNION ALL SELECT '[DEMO-ALLAN-L09] Delivery coordination', 85000.00, 'Demo ledger entry for delivery coordination.'
    UNION ALL SELECT '[DEMO-ALLAN-L10] Seasonal review supplies', 115000.00, 'Demo ledger entry for seasonal review supplies.'
) AS demo_costs
WHERE @allan_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM extra_costs WHERE created_by = @allan_id AND notes = demo_costs.notes);

SET @manager_id = (SELECT user_id FROM users WHERE user_name = 'Ben' AND role = 4 AND status = 1 ORDER BY user_id LIMIT 1);
SET @manager_name = (SELECT user_name FROM users WHERE user_id = @manager_id);

INSERT INTO manager_activity_log (actor_id, actor_name, action_type, target_type, target_id, notes)
SELECT @manager_id, @manager_name, action_type, target_type, target_id, activity_notes
FROM (
    SELECT 'staff_reviewed' AS action_type, 'staff' AS target_type, 1 AS target_id, '[DEMO-MANAGER-A01] Reviewed staff account records.' AS activity_notes
    UNION ALL SELECT 'subscription_reviewed', 'subscription_plan', 2, '[DEMO-MANAGER-A02] Reviewed active subscription plans.'
    UNION ALL SELECT 'cost_reviewed', 'extra_cost', 3, '[DEMO-MANAGER-A03] Reviewed the operating-cost ledger.'
    UNION ALL SELECT 'tax_reviewed', 'tax_rule', 4, '[DEMO-MANAGER-A04] Reviewed quantity-based tax rules.'
    UNION ALL SELECT 'farmer_reviewed', 'farmer', 5, '[DEMO-MANAGER-A05] Reviewed farmer approval records.'
    UNION ALL SELECT 'report_reviewed', 'supervisor_report', 6, '[DEMO-MANAGER-A06] Reviewed supervisor field reports.'
    UNION ALL SELECT 'payroll_reviewed', 'payroll', 7, '[DEMO-MANAGER-A07] Reviewed payroll entries.'
    UNION ALL SELECT 'inquiry_reviewed', 'inquiry', 8, '[DEMO-MANAGER-A08] Reviewed customer inquiry activity.'
    UNION ALL SELECT 'transaction_reviewed', 'transaction', 9, '[DEMO-MANAGER-A09] Reviewed recent transaction summaries.'
    UNION ALL SELECT 'dashboard_reviewed', 'dashboard', 10, '[DEMO-MANAGER-A10] Completed the operations dashboard review.'
) AS manager_activity
WHERE @manager_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM manager_activity_log WHERE actor_id = @manager_id AND notes = manager_activity.activity_notes);

INSERT INTO staff_payroll (user_id, staff_name, staff_role, email, phone, salary, status, paid_at)
SELECT NULL, staff_name, staff_role, email, phone, salary, status, IF(status = 1, NOW(), NULL)
FROM (
    SELECT '[DEMO-MANAGER-P01] Field Coordinator' AS staff_name, 'Field Coordinator' AS staff_role, 'demo-coordinator-01@example.test' AS email, '+256700000101' AS phone, 850000.00 AS salary, 1 AS status
    UNION ALL SELECT '[DEMO-MANAGER-P02] Accounts Assistant', 'Accounts Assistant', 'demo-accounts-02@example.test', '+256700000102', 780000.00, 1
    UNION ALL SELECT '[DEMO-MANAGER-P03] Market Liaison', 'Market Liaison', 'demo-liaison-03@example.test', '+256700000103', 820000.00, 1
    UNION ALL SELECT '[DEMO-MANAGER-P04] Records Clerk', 'Records Clerk', 'demo-records-04@example.test', '+256700000104', 650000.00, 1
    UNION ALL SELECT '[DEMO-MANAGER-P05] Logistics Assistant', 'Logistics Assistant', 'demo-logistics-05@example.test', '+256700000105', 740000.00, 1
    UNION ALL SELECT '[DEMO-MANAGER-P06] Seasonal Support', 'Seasonal Support', 'demo-seasonal-06@example.test', '+256700000106', 590000.00, 0
    UNION ALL SELECT '[DEMO-MANAGER-P07] Produce Auditor', 'Produce Auditor', 'demo-auditor-07@example.test', '+256700000107', 800000.00, 1
    UNION ALL SELECT '[DEMO-MANAGER-P08] Customer Desk', 'Customer Desk', 'demo-customer-08@example.test', '+256700000108', 700000.00, 1
    UNION ALL SELECT '[DEMO-MANAGER-P09] Inventory Clerk', 'Inventory Clerk', 'demo-inventory-09@example.test', '+256700000109', 680000.00, 0
    UNION ALL SELECT '[DEMO-MANAGER-P10] Compliance Assistant', 'Compliance Assistant', 'demo-compliance-10@example.test', '+256700000110', 760000.00, 1
) AS manager_payroll
WHERE @manager_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM staff_payroll WHERE staff_name = manager_payroll.staff_name);

INSERT INTO subscription_plans (plan_name, description, amount, duration_days, status, created_by)
SELECT plan_name, plan_description, amount, duration_days, 1, @manager_id
FROM (
    SELECT '[DEMO-MANAGER-S01] Starter Farmer' AS plan_name, 'Demo starter subscription plan.' AS plan_description, 25000.00 AS amount, 30 AS duration_days
    UNION ALL SELECT '[DEMO-MANAGER-S02] Growing Farmer', 'Demo growing farm subscription plan.', 50000.00, 60
    UNION ALL SELECT '[DEMO-MANAGER-S03] Market Farmer', 'Demo market access subscription plan.', 75000.00, 90
    UNION ALL SELECT '[DEMO-MANAGER-S04] Cooperative Plus', 'Demo cooperative support subscription plan.', 100000.00, 120
    UNION ALL SELECT '[DEMO-MANAGER-S05] Harvest Partner', 'Demo harvest partner subscription plan.', 125000.00, 150
    UNION ALL SELECT '[DEMO-MANAGER-S06] Seasonal Grower', 'Demo seasonal grower subscription plan.', 150000.00, 180
    UNION ALL SELECT '[DEMO-MANAGER-S07] Enterprise Farm', 'Demo enterprise farm subscription plan.', 200000.00, 365
    UNION ALL SELECT '[DEMO-MANAGER-S08] Family Plot', 'Demo family plot subscription plan.', 35000.00, 45
    UNION ALL SELECT '[DEMO-MANAGER-S09] Produce Collective', 'Demo produce collective subscription plan.', 90000.00, 90
    UNION ALL SELECT '[DEMO-MANAGER-S10] Premium Market', 'Demo premium market subscription plan.', 175000.00, 365
) AS manager_plans
WHERE @manager_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM subscription_plans WHERE plan_name = manager_plans.plan_name);

INSERT INTO tax_rules (rule_name, rate_percent, min_quantity, max_quantity, applies_to, applies_unit, status)
SELECT rule_name, rate_percent, min_quantity, max_quantity, 'all', applies_unit, 1
FROM (
    SELECT '[DEMO-MANAGER-T01] Small produce levy' AS rule_name, 1.00 AS rate_percent, 1 AS min_quantity, 5 AS max_quantity, 'kilogram' AS applies_unit
    UNION ALL SELECT '[DEMO-MANAGER-T02] Medium produce levy', 1.50, 6, 10, 'kilogram'
    UNION ALL SELECT '[DEMO-MANAGER-T03] Large produce levy', 2.00, 11, 25, 'kilogram'
    UNION ALL SELECT '[DEMO-MANAGER-T04] Bulk produce levy', 2.50, 26, NULL, 'kilogram'
    UNION ALL SELECT '[DEMO-MANAGER-T05] Milk handling levy', 1.00, 1, 10, 'litre'
    UNION ALL SELECT '[DEMO-MANAGER-T06] Bulk milk levy', 1.75, 11, NULL, 'litre'
    UNION ALL SELECT '[DEMO-MANAGER-T07] Grain handling levy', 1.25, 1, 20, 'gram'
    UNION ALL SELECT '[DEMO-MANAGER-T08] Bulk grain levy', 2.25, 21, NULL, 'gram'
    UNION ALL SELECT '[DEMO-MANAGER-T09] Packaged item levy', 1.50, 1, 10, 'piece'
    UNION ALL SELECT '[DEMO-MANAGER-T10] Bulk packaged levy', 2.00, 11, NULL, 'each'
) AS manager_taxes
WHERE NOT EXISTS (SELECT 1 FROM tax_rules WHERE rule_name = manager_taxes.rule_name);