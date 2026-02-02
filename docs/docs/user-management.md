# User Management

Managing members in Orange Absence involves roles, permissions, and division assignments.

## Roles & Permissions

The system uses `spatie/laravel-permission`. There are three primary tiers:

| Role | Access Level | Responsibilities |
| :--- | :--- | :--- |
| **Super Admin** | Full | System config, Database management, Global logs. |
| **Secretary** | Division | Managing their own division's attendance and finances. |
| **Member** | Personal | View own attendance and cash logs. |

### Pro-Tip: Adding New Secretaries
When adding a new secretary, ensure you assign them to a **Division**. Without a division assignment, a secretary might see empty data or encounter errors in division-specific scopes.

## Account Suspension

If a member is suspended:
1. They **cannot** scan QR codes.
2. They **cannot** login to the member portal.
3. Their attendance history remains stored but they are hidden from active attendance lists.

:::info Re-activation
Suspension is a simple boolean flag in the `users` table. Re-activating a user immediately restores all their access without data loss.
:::

## Division Logic

Every user belongs to exactly one division. The division determines:
- Which **Schedules** the user is eligible for.
- Which **Secretary** can view their data.
- The amount of **Kas** (Cash) they need to pay weekly.
