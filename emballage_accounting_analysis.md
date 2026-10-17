# Bottle vs Crate Accounting: Analysis Report

**Context:** Invoice `V00002` (vente en détail), client `1004`, packaging type `Casier SABC GM 12 Bouteilles` (id 1).
**Source:** Live DB `depot_la_cachette` + code in [VentesModel.php](file:///c:/xampp/htdocs/webapp/app/Models/VentesModel.php) and [EmballagesModel.php](file:///c:/xampp/htdocs/webapp/app/Models/EmballagesModel.php).

---

## 1. Summary

You are right. The system has **one ledger** where it needs **two**.

- **Bottles (glass/verre):** tracked correctly. Every bottle is accounted for.
- **Crates (the plastic casier/shell):** not tracked on its own. The system treats a crate as "12 bottles". So:
  1. When a crate is opened for retail sales, part of that crate goes missing from the count (**12.75 crates** is not something you can have physically).
  2. When a crate is fully emptied through retail sales, the empty crate **never goes back** into `empty_crates`. It is lost for good.
  3. When a client takes bottles **inside a crate** (retail of 6 or more, or a *demi* in wholesale), there is no field to record that crate as a debt.
  4. `client_emballage_debts.crates_due` is **not really a crate debt**. It is just `bottles ÷ 12`. 12 loose bottles turn into "1 casier" even if no crate ever left the depot.

---

## 2. Rebuilding the actual data (SABC GM, type 1)

### 2.1 What happened

| Step | Event | Effect in DB |
|---|---|---|
| Init | `Initial_Stock` | `empty_crates` = 100 |
| A00001 | Bought 20 EXP + 20 BFT full crates, gave 40 empties to supplier | full +40, empty −40 → 60 |
| V00001 | Sold 7 EXP + 8 BFT crates, 14 returned | full −15, empty +14 → 74, client owes **1 crate** |
| V00002 | 3 EXP `sur_place` (returned 3), 3 BFT `dette` (emporté) | full −0.25 −0.25, `loose_bottles` +3, client owes **+3 btl** |

### 2.2 Crate shells: physical count vs system count

Assumption: the 3 BFT bottles were carried in hand, not in a crate.

| Location | Physical shells | System count |
|---|---|---|
| Empty crates at depot | 74 | 74 |
| EXP: 12 full + 1 open crate (9 full) | **13** | 12.75 |
| BFT: 11 full + 1 open crate (9 full) | **12** | 11.75 |
| Client 1004 | 1 | 1 |
| **Total** | **100** ✅ | **99.5** ❌ |

> [!WARNING]
> **0.5 crate is missing** (0.25 for each open crate). Your emballage screen shows `24,5 + 74 + 1 = 99,5 casiers` and values them at 358 200 FCFA. Physically there are 100 crates.
> If the 3 BFT bottles left **inside a crate**, the client really holds 2 crates and the depot has 98. The system still says client = 1 and total = 99.5.

### 2.3 Glass bottles: physical vs system

| Location | Physical bottles | System |
|---|---|---|
| Full bottles (153 EXP + 141 BFT) | 294 | (12.75+11.75)×12 = 294 |
| Empties inside crates | 888 | 74×12 = 888 |
| Loose empties (3 EXP drunk on site) | 3 | `loose_bottles` = 3 |
| Client 1004 | 15 | 1×12 + 3 = 15 |
| **Total** | **1200** ✅ | **1200** ✅ |

**Result:** the bottle ledger is correct. Only the crate ledger is wrong. This shows the problem is in the **data model**, not a calculation error.

### 2.4 Reading "12 casier(s) + 9 btls"

The stock page reads correctly for **liquid**: 153 full bottles. Physically, the 9 bottles sit in a 13th crate with 3 empty slots, and that crate is not counted anywhere.

---

## 3. Root causes in the code

| # | Where | Problem |
|---|---|---|
| R1 | Product stock = `stock_equivalent` in fractional crates ([L648](file:///c:/xampp/htdocs/webapp/app/Models/VentesModel.php#L648)) | Full stock is really a bottle count shown as crates. The open crate's shell is counted as 0.75. |
| R2 | `getPackagingTypes()` `full_crates_stock` ([L16-25](file:///c:/xampp/htdocs/webapp/app/Models/EmballagesModel.php#L16-L25)) | Adds up the fractions across all products. Two open crates (EXP + BFT) count as 1.5 instead of 2. |
| R3 | Retail sale ([L671-682](file:///c:/xampp/htdocs/webapp/app/Models/VentesModel.php#L671-L682)) | `crates_out` is always 0. You cannot record "given in a crate". |
| R4 | Wholesale *demi* ([L368-369](file:///c:/xampp/htdocs/webapp/app/Models/VentesModel.php#L368-L369)) | `crates_out = intval(qty)`. The crate a *demi* leaves in is ignored. Only 6 bottles are recorded. |
| R5 | Debt normalization ([L577-580](file:///c:/xampp/htdocs/webapp/app/Models/VentesModel.php#L577-L580), [L346-352](file:///c:/xampp/htdocs/webapp/app/Models/EmballagesModel.php#L346-L352)) | `crates_due = floor(total_bottles / 12)`. Crate and bottle debts are merged into one number. |
| R6 | Retail debt **does not** normalize ([L815-819](file:///c:/xampp/htdocs/webapp/app/Models/VentesModel.php#L815-L819)) | It only adds loose bottles. Wholesale and returns *do* normalize. Same client, two different rules. |
| R7 | Return of "1 crate" ([L348](file:///c:/xampp/htdocs/webapp/app/Models/EmballagesModel.php#L348)) | 1 crate returned is assumed to hold 12 bottles. An empty shell brought back alone wipes out 12 bottles of debt. |
| R8 | No "crate freed" event | When an open crate's last bottle is sold retail, `empty_crates` is never incremented. |
| R9 | Valuation ([L59-60](file:///c:/xampp/htdocs/webapp/app/Models/EmballagesModel.php#L59-L60)) | `default_cost` (3600) is one price for "crate + 12 bottles". Loose bottles are worth 0. Fractional crates are valued too. |

---

## 4. Failure scenarios (what goes wrong next)

| Scenario | Physical reality | System says | Error |
|---|---|---|---|
| **S1.** The other 9 EXP are sold `sur_place` | +1 empty crate (with 12 empties) | full −0.75, loose +9 = 12 loose, empty_crates unchanged | **1 crate lost for good** |
| **S2.** Client takes 6 BFT in a crate (retail) | Client owes 1 crate + 6 btl | Client owes 6 btl | Crate debt **under**-stated |
| **S3.** Wholesale 2 casiers + 1 demi, all in crates | 3 crates + 30 btl out | crates_out = 2, bottles_out = 30 | Crate debt under-stated by 1 |
| **S4.** Client 1004 takes 9 more BFT emporté (no crate), then buys wholesale with full return | Owes 1 crate + 24 btl | Retail: `1 casier + 12 btl`. After next wholesale it normalizes to `2 casiers + 0` | Crate debt **over**-stated |
| **S5.** Client returns 1 empty shell (the crate from S2) | Debt: 6 btl left | 12 btl removed from debt → 0 | 6 bottles forgiven by mistake |
| **S6.** 3 EXP + 3 BFT + 3 Orijin + 3 Manyan carried in **one** crate | 1 crate (of the type used) + 3 btl for each brand's bottle type | 12 btl spread over packaging types, 0 crates | Crate missing. Crate type unknown. |

> [!IMPORTANT]
> S6 shows that **crate lending belongs to the invoice, not to the product line**. One crate can carry bottles from several products and even several packaging types. The bottle debt goes to each bottle's packaging type. The crate debt goes to **the crate actually handed over**.

---

## 5. Proposed model: two separate ledgers

### 5.1 Core rule

```
CRATE (shell)  : physical plastic casier, counted as a whole number, never derived from bottles
BOTTLE (glass) : physical glass, full or empty, counted as a whole number
```

Full product stock stays as it is (a bottle count shown as `X casiers + Y btls`). The crate count for **liquid** stock is derived as `ceil(full_bottles / factor)` for each product. That fixes R1/R2 for reporting.

### 5.2 Schema changes (proposal)

| Table | Change |
|---|---|
| `emballage_stock` | Keep `empty_crates` (empty shells only) and rename/redefine `loose_bottles` → `empty_bottles` (**all** empty glass, whether in a crate or not). |
| `client_emballage_debts` / `supplier_emballage_debts` | `crates_due` = **shells only**, `bottles_due` = **glass only**. Remove all `÷ factor` normalization. |
| `sale_items` | Keep `bottles_out` / `bottles_returned`. `crates_out` / `crates_returned` now mean **shells only**. |
| **NEW** `sale_crate_loans` | `(sale_id, packaging_type_id, crates_lent, crates_returned)`: crates lent per invoice, not per line (handles S6). |
| `packaging_types` | Split `default_cost` into `crate_cost` and `bottle_cost` (consigne). |

### 5.3 Business rules

| Case | Bottles | Crates (shells) |
|---|---|---|
| Wholesale `casier` × N | out = N×12 | lent = N (default) |
| Wholesale `demi` | out = 6 | lent = 1 (default, editable) |
| Retail ≥ threshold (client says **6**) | out = qty | lent = 1 suggested, editable |
| Retail < threshold | out = qty | lent = 0 |
| Return of "casier complet" (UI shortcut) | returned += 12 | returned += 1 |
| Return of shell only / loose glass | entered separately | entered separately |
| **Crate freed**: `ceil(stock/factor)` drops after a sale | – | `empty_crates += Δ` (automatic, fixes R8) |
| Crate lent during retail | – | `empty_crates −= lent`, unless it is the freed open crate |

### 5.4 UI changes

- **En détail and en gros:** add an invoice-level block **"Emballages prêtés"** (crate type + quantity). Pre-fill it with the threshold rule.
- **Restitution form:** two inputs, *Casiers (vides)* and *Bouteilles vides*, plus a *"+ casier complet"* button that adds 1 + 12.
- **Debt display:** `1 casier · 15 btl` instead of the normalized `1 casier + 3 btl`.

### 5.5 Migration

1. Rebuild client debts from `sale_items` history (shells = `crates_out − crates_returned`, glass = `bottles_out − bottles_returned`). Do **not** convert between the two.
2. Run a one-time **physical crate count** for each packaging type and post an adjustment to fix the 0.5-crate drift and any shells already lost.

---

## 6. Side findings (bugs seen during the analysis)

- [addRetailSale L732-735](file:///c:/xampp/htdocs/webapp/app/Models/VentesModel.php#L732-L735): `$cashAccountId` is undefined on a credit retail sale (PHP warning, NULL inserted).
- [addRetailSale L755](file:///c:/xampp/htdocs/webapp/app/Models/VentesModel.php#L755): `$userId` is set again to `$data['user_id'] ?? 1`, which overrides the session-based value set at L619.

---

## 7. Questions for the client before building

1. **Threshold:** should the crate always be suggested at **6 bottles or more**, and can the cashier always change it?
2. **Mixed crates:** can a SABC crate carry Guinness/UCB bottles to a client? If so, which crate type does the client owe?
3. **Open crates:** do staff combine two half-empty crates of the same brand into one? This changes the `ceil()` rule.
4. **Prices:** what is the separate value of an **empty crate** and of **one bottle** (for valuation and for billing lost emballages)?
5. **Sur place:** do the empties from bottles drunk on site go back into the open crate, or into a separate loose pile?
