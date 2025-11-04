-- ⚠️ WARNING: This will DELETE all records from October and November 2025
-- BACKUP YOUR DATABASE FIRST before running this!

-- Step 1: PREVIEW - Check how many records will be deleted (RUN THIS FIRST)
SELECT 
    COUNT(*) as total_records_to_delete,
    COUNT(CASE WHEN DATE_FORMAT(punch_time, '%Y-%m') = '2025-10' THEN 1 END) as october_records,
    COUNT(CASE WHEN DATE_FORMAT(punch_time, '%Y-%m') = '2025-11' THEN 1 END) as november_records
FROM device_attendances
WHERE (punch_time >= '2025-10-01 00:00:00' AND punch_time < '2025-12-01 00:00:00');

-- Step 2: DELETE all records from October and November 2025
DELETE FROM device_attendances
WHERE punch_time >= '2025-10-01 00:00:00' 
  AND punch_time < '2025-12-01 00:00:00';

-- Alternative using DATE_FORMAT (if you prefer):
-- DELETE FROM device_attendances
-- WHERE DATE_FORMAT(punch_time, '%Y-%m') IN ('2025-10', '2025-11');

