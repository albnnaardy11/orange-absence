# Finances & Cash Logs

The financial module automates the collection and tracking of divisional "Kas".

## Automated Weekly Billing

Every Monday, a background job calculates the dues for each active member.

- **Calculation**: Based on the `weekly_fee` set in the `divisions` table.
- **Arrears**: If a member hasn't paid for 3 weeks, they are flagged in the Secretary's "Red List".

## Managing Cash Logs

Every payment should be recorded through the Admin Panel. 
1. **Source**: The member paying.
2. **Amount**: Total paid (can be partial).
3. **Admin Notes**: Used for tracking physical receipts or bank transfers.

:::danger Financial Integrity
Once a transaction is "Finalized", it cannot be deleted by a Secretary. Only a Super Admin with database access can reverse a finalized transaction to prevent embezzlement.
:::

## Best Practices

### Audit Trail
Always use the **Points** system as a reward. The system can be configured to automatically award points to users who pay their Kas on time.

### Partial Payments
If a member cannot pay in full, the system tracks the remaining balance. The Secretary should record exactly what was received to keep the "Total Cash in Hand" accurate.
