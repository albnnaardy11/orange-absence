# Attendance & Scheduling

The core of Orange Absence is the smart attendance system. It combines GPS location and rotating QR codes to prevent cheating.

## How Geofencing Works

The system calculates the distance between the **User's Phone** and the **Schedule's Latitude/Longitude** using the Haversine formula.

- **Radius**: Default is 50 meters (configurable via env).
- **Grace Period**: Users can scan up to 15 minutes before and 30 minutes after the schedule starts.

:::tip Advice: GPS Accuracy
Explain to users that they should stay outdoors or near windows when scanning. Indoor GPS jitter can sometimes put them 100m away, causing a "Outside Area" error.
:::

## Scheduling Logic

Schedules are recurring. A schedule is defined by:
- **Location**: Lat/Long coordinates.
- **Time**: Start and end time.
- **Division**: Which group this schedule is for.

### The "Auto-Absent" Rule
If a user does not scan during the schedule time, the system will not automatically mark them as "Absent". The Secretary must review the "Missing" list and confirm the status at the end of the day.

## QR Code Security

QR codes are **NOT static**. Each scan session generates a unique signature. 
1. If a user tries to screenshot and send the QR to a friend, the friend will likely fail the **Geofence Check**.
2. If the secretary regenerates the code, the old code is invalidated immediately.
