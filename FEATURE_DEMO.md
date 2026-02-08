# Gross Salary Daily Entries - Feature Demo

## 🎬 Feature Demo: From Start to Finish

### STEP 1: Navigate to Gross Salary Page

```
URL: /payroll/gross
```

**You see:**
- List view with filters at the top
- Summary table of all gross salary records
- Summary statistics showing totals

---

### STEP 2: Set Your Filters

```
Period:     [1-15 ▼]          (First half of January)
Month:      [January 2026]
Department: [Sales ▼]         (Optional)
Employee:   [All Employees ▼] (Optional)
```

Click **[Filter]** button

---

### STEP 3: Generate Entries (if needed)

```
Click [Generate] button

↓

System creates/updates gross salary entries from attendance logs

↓

Success: "Generated 5 new records and updated 2 existing records"
```

---

### STEP 4: Click "View Daily Entries"

```
Filters show:
- Period: Jan 1-15, 2026
- Department: Sales
- Employees: 5 selected
- Status: Show completed

[Filter] [View Daily Entries] ← CLICK HERE
```

---

## 📊 STEP 5: See the Daily Breakdown

### The Page Shows:

```
╔══════════════════════════════════════════════════════════════════════╗
║ Gross Salary Entries - Daily Breakdown                     [← Back]  ║
║ Period: Jan 1, 2026 - Jan 15, 2026                                  ║
╚══════════════════════════════════════════════════════════════════════╝

╔══════════════════════════════════════════════════════════════════════╗
║ EMPLOYEE #1: John Doe (ID: 1)                         Status: [Draft]║
║ Department: Sales                                                    ║
╠════════╦═══════╦═══════╦═══════╦═══════╦═══════╦════════╦═══════════╣
║ Day    ║ Jan 5 ║ Jan 6 ║ Jan 7 ║ Jan 8 ║ Jan 9 ║ ...    ║  Total    ║
║        ║ (Mon) ║ (Tue) ║ (Wed) ║ (Thu) ║ (Fri) ║        ║  Days     ║
╠════════╬═══════╬═══════╬═══════╬═══════╬═══════╬════════╬═══════════╣
║ Hours: ║ 8.51h ║ 8.0h  ║ 7.5h  ║ 8.0h  ║ 8.0h  ║ ...    ║  40.01h   ║
║        ║ 7:25- ║ 8:00- ║ 8:15- ║ 7:45- ║ 8:00- ║        ║           ║
║        ║ 12:01 ║ 12:30 ║ 12:45 ║ 12:15 ║ 12:30 ║        ║           ║
║        ║ 1:00- ║ 1:00- ║ 1:00- ║ 1:00- ║ 1:00- ║        ║           ║
║        ║ 5:15  ║ 5:15  ║ 5:15  ║ 5:15  ║ 5:15  ║        ║           ║
╠════════╬═══════╬═══════╬═══════╬═══════╬═══════╬════════╬═══════════╣
║ Days:  ║ 1.0   ║ 1.0   ║ 0.94  ║ 1.0   ║ 1.0   ║ ...    ║ 5.0 days  ║
╚════════╩═══════╩═══════╩═══════╩═══════╩═══════╩════════╩═══════════╝

Summary:
┌──────────────────┬──────────────────┬──────────────────┬──────────────────┐
│ Total Hours      │ Days Worked      │ Hourly Rate      │ Daily Rate       │
│ 40.01h           │ 5.0 days         │ ₱227.27/hr       │ ₱1,818.18/day    │
└──────────────────┴──────────────────┴──────────────────┴──────────────────┘
└─ Gross Salary: ₱9,090.90


╔══════════════════════════════════════════════════════════════════════╗
║ EMPLOYEE #2: Jane Smith (ID: 2)                        Status: [Final]║
║ Department: Sales                                                    ║
╠════════╦═══════╦═══════╦═══════╦═══════╦═══════╦════════╦═══════════╣
║ Day    ║ Jan 5 ║ Jan 6 ║ Jan 7 ║ Jan 8 ║ Jan 9 ║ ...    ║  Total    ║
║        ║ (Mon) ║ (Tue) ║ (Wed) ║ (Thu) ║ (Fri) ║        ║  Days     ║
╠════════╬═══════╬═══════╬═══════╬═══════╬═══════╬════════╬═══════════╣
║ Hours: ║ 8.0h  ║ 8.5h  ║ 8.0h  ║ 8.0h  ║ 8.0h  ║ ...    ║  40.5h    ║
║        ║ ...   ║ ...   ║ ...   ║ ...   ║ ...   ║        ║           ║
╠════════╬═══════╬═══════╬═══════╬═══════╬═══════╬════════╬═══════════╣
║ Days:  ║ 1.0   ║ 1.0   ║ 1.0   ║ 1.0   ║ 1.0   ║ ...    ║ 5.0 days  ║
╚════════╩═══════╩═══════╩═══════╩═══════╩═══════╩════════╩═══════════╝

Summary:
Gross Salary: ₱9,090.90


╔══════════════════════════════════════════════════════════════════════╗
║ EMPLOYEE #3: Mike Johnson (ID: 3)                    Status: [Draft] ║
║ Department: Sales                                                    ║
╠════════╦═══════╦═══════╦═══════╦═══════╦═══════╦════════╦═══════════╣
║ Day    ║ Jan 5 ║ Jan 6 ║ Jan 7 ║ Jan 8 ║ Jan 9 ║ ...    ║  Total    ║
║        ║ (Mon) ║ (Tue) ║ (Wed) ║ (Thu) ║ (Fri) ║        ║  Days     ║
╠════════╬═══════╬═══════╬═══════╬═══════╬═══════╬════════╬═══════════╣
║ Hours: ║ 4.0h  ║ 8.0h  ║ 8.0h  ║ 8.0h  ║ 8.0h  ║ ...    ║  36.0h    ║
║        ║ ...   ║ ...   ║ ...   ║ ...   ║ ...   ║        ║           ║
╠════════╬═══════╬═══════╬═══════╬═══════╬═══════╬════════╬═══════════╣
║ Days:  ║ 0.5   ║ 1.0   ║ 1.0   ║ 1.0   ║ 1.0   ║ ...    ║ 4.5 days  ║
╚════════╩═══════╩═══════╩═══════╩═══════╩═══════╩════════╩═══════════╝

Summary:
Gross Salary: ₱8,181.82

... (and 2 more employees)
```

---

## 🎯 Key Features in Action

### 1. Day Columns
```
Each day shows:
- Date (Jan 5)
- Day number (5)
- Day abbreviation (Mon)
```

### 2. Hours Per Day
```
- Total hours for the day (e.g., 8.51h)
- Time punch entries:
  - AM: 7:25 - 12:01
  - PM: 1:00 - 5:15
  - (Optional) OT: 6:00 - 7:30
```

### 3. Days Worked Calculation
```
Hours >= 8.0    → 1.0 day worked
Hours 4.0-7.99  → 0.5 day worked
Hours < 4.0     → Proportional (e.g., 6h = 0.75 day)

Example:
- 8.51 hours → 1.0 day
- 7.5 hours  → 0.94 days
- 4.0 hours  → 0.5 days
```

### 4. Totals
```
Total Hours: 40.01h
Total Days:  5.0 days
```

### 5. Calculations
```
Hourly Rate = Monthly Salary / 22 working days / 8 hours/day
             = $5,000 / 22 / 8
             = $227.27/hour

Daily Rate = Hourly Rate × 8
           = $227.27 × 8
           = $1,818.18/day

Gross Salary = Days Worked × Daily Rate
             = 5.0 × $1,818.18
             = $9,090.90
```

---

## 💡 Real-World Example

### Scenario:
- Period: January 1-15, 2026 (First half)
- Department: Sales
- 5 Employees

### Results:
```
SUMMARY FOR JANUARY 1-15, 2026 - SALES DEPARTMENT

Employee         │ Days Worked │ Gross Salary
─────────────────┼─────────────┼──────────────
John Doe         │ 5.0 days    │ ₱9,090.90
Jane Smith       │ 5.0 days    │ ₱9,090.90
Mike Johnson     │ 4.5 days    │ ₱8,181.82
Sarah Lee        │ 4.0 days    │ ₱7,272.73
Tom Brown        │ 5.0 days    │ ₱9,090.90
─────────────────┼─────────────┼──────────────
TOTAL            │ 23.5 days   │ ₱42,727.25
```

---

## 🚀 Benefits

✅ **Visual Clarity**: See exactly which days were worked  
✅ **Detail Oriented**: View time entries for verification  
✅ **Easy Verification**: Confirm calculations manually if needed  
✅ **Department View**: See all department employees in one report  
✅ **Period Flexibility**: View any payroll period  
✅ **Status Tracking**: Know the status of each record (Draft/Final/Completed)  
✅ **Professional Format**: Ready for presentations or audits  
✅ **Responsive**: Works on desktop, tablet, and mobile (with scroll)  

---

## 🔄 Workflow

```
1. User selects filters
   ↓
2. System generates gross entries from attendance logs
   ↓
3. User clicks "View Daily Entries"
   ↓
4. Detailed breakdown displayed:
   - Each day as a column
   - Hours and times per day
   - Days worked calculated
   - Gross salary computed
   ↓
5. User can:
   - Review accuracy
   - Print or export
   - Change status
   - Approve/reject
```

---

## 📱 Responsive Design

### Desktop View
```
Full table with all days visible
May need horizontal scroll if many days
```

### Tablet View
```
Fewer columns visible
Horizontal scroll available
Touch-friendly buttons
```

### Mobile View
```
Vertical layout for filters
Scroll table horizontally
Large tap targets
```

---

## 📊 Data Quality

The view shows:
- ✅ Source data from attendance logs
- ✅ Calculated values with 2 decimal places
- ✅ Currency formatting (₱)
- ✅ Time formatting (HH:MM)
- ✅ Day/date formatting (M d, DDD)
- ✅ Status indicators

---

## 🎓 Learning the Numbers

### Example Employee: John Doe

**Raw Data:**
- Monthly Salary: ₱5,000
- Days Worked: 5 (full days)
- Total Hours: 40 hours

**Calculations Shown:**
```
Hourly Rate    = ₱5,000 / 22 / 8 = ₱227.27/hour
Daily Rate     = ₱227.27 × 8 = ₱1,818.18/day
Gross Salary   = 5 days × ₱1,818.18 = ₱9,090.90
```

**You Can Verify:**
```
- Check hours per day add up to 40 total
- Verify each day shows proper time entries
- Confirm daily calculation (24 hours in at least 8 entries)
- See exactly which days were worked
```

---

## ✨ Summary

The **Daily Entries View** provides a comprehensive, easy-to-read breakdown of gross salary calculations with:

1. **Days as columns** - Each working day clearly labeled
2. **Hour details** - Exact hours and times for each day
3. **Days calculation** - How many days each employee worked
4. **Gross salary** - Total earned amount
5. **All filtered employees** - In one consolidated view
6. **Professional format** - Ready for review, audit, or distribution

**Perfect for:**
- Management review
- Employee verification
- Payroll audits
- Department reporting
- Financial analysis
- Compliance documentation

---

**Feature Status:** ✅ READY TO USE  
**Access:** [Gross Salary Page] → [View Daily Entries Button]  
**URL:** `/gross/entries`
