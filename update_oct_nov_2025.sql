-- Add 9 seconds to punch_time for OUT records in October and November 2025
UPDATE device_attendances 
SET punch_time = DATE_ADD(punch_time, INTERVAL 9 SECOND) 
WHERE device_type = 'OUT' 
  AND DATE_FORMAT(punch_time, '%Y-%m') IN ('2025-10', '2025-11');

-- Add 43 seconds to punch_time for IN records in October and November 2025
UPDATE device_attendances 
SET punch_time = DATE_ADD(punch_time, INTERVAL 43 SECOND) 
WHERE device_type = 'IN' 
  AND DATE_FORMAT(punch_time, '%Y-%m') IN ('2025-10', '2025-11');

-- Alternative using date range (if you prefer):
-- UPDATE device_attendances 
-- SET punch_time = DATE_ADD(punch_time, INTERVAL 9 SECOND) 
-- WHERE device_type = 'OUT' 
--   AND punch_time >= '2025-10-01 00:00:00' 
--   AND punch_time < '2025-12-01 00:00:00';
--
-- UPDATE device_attendances 
-- SET punch_time = DATE_ADD(punch_time, INTERVAL 43 SECOND) 
-- WHERE device_type = 'IN' 
--   AND punch_time >= '2025-10-01 00:00:00' 
--   AND punch_time < '2025-12-01 00:00:00';

