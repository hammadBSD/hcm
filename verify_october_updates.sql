-- Query to verify the October 2025 updates
-- This will show you October 2025 records to verify the changes were applied

-- View October 2025 records with punch_code 430
SELECT 
    id,
    punch_code,
    device_type,
    punch_time,
    DATE_FORMAT(punch_time, '%Y-%m-%d %H:%i:%s') as formatted_time
FROM device_attendances
WHERE punch_code = 430
  AND punch_time >= '2025-10-01 00:00:00' 
  AND punch_time < '2025-11-01 00:00:00'
ORDER BY punch_time DESC
LIMIT 50;

-- Or if you want to see all October records for all punch codes:
SELECT 
    device_type,
    COUNT(*) as count,
    MIN(punch_time) as earliest_punch,
    MAX(punch_time) as latest_punch
FROM device_attendances
WHERE punch_time >= '2025-10-01 00:00:00' 
  AND punch_time < '2025-11-01 00:00:00'
GROUP BY device_type;

