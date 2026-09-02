# -*- coding: utf-8 -*-
"""
Depot La Cachette V4 — Générateur openpyxl
Produit : Depot_La_Cachette_V4.xlsm (structure + design + données Ref)
Le VBA est injecté via vba_module.bas importé dans Excel manuellement (Alt+F11)
ou via un script win32com minimal en fin de script.
"""

import os
import sys
from openpyxl import Workbook
from openpyxl.styles import (PatternFill, Font, Alignment, Border, Side,
                              GradientFill)
from openpyxl.styles.numbers import FORMAT_DATE_DDMMYY
from openpyxl.utils import get_column_letter
from openpyxl.worksheet.datavalidation import DataValidation
from openpyxl.drawing.image import Image as XLImage
from openpyxl.worksheet.page import PageMargins

print("=== DEPOT LA CACHETTE V4 — Génération openpyxl ===")

# ================================================================
# PATHS
# ================================================================
CHEMIN = r"c:\Users\PC USER\Downloads\Depots La Cachette"
FICHIER_OUT = os.path.join(CHEMIN, "Depot_La_Cachette_V4.xlsm")
LOGO_PATH = r"C:\Users\PC USER\.gemini\antigravity-ide\brain\330124b5-7c67-4a69-a08a-fc07599fbc6c\depot_logo_1786835649623.jpg"
MDP = "cachette"
VERSION = "V4.0 — Aout 2026"

# ================================================================
# COLORS (hex)
# ================================================================
NAVY       = "0D1B2A"
NAVY_MID   = "16283C"
AMBER      = "F4A423"
AMBER_DRK  = "B47800"
AMBER_LGT  = "FEF3C7"
SLATE      = "1E2D3D"
SLATE_LGT  = "34495E"
BLUE_LGT   = "EFF6FF"
GREEN      = "10B981"
GREEN_LGT  = "D1FAE5"
RED        = "EF4444"
RED_LGT    = "FEE2E2"
ORANGE     = "F59E0B"
WHITE      = "FFFFFF"
GRAY_DRK   = "1F2937"
GRAY_MID   = "6B7280"
GRAY_LGT   = "F3F4F6"
BORDER_C   = "CBD5E1"
ROW_ALT    = "F8FAFC"
TEAL       = "14B8A6"
PURPLE     = "8B5CF6"

def fill(hex_color):
    return PatternFill("solid", fgColor=hex_color)

def font(color=None, bold=False, size=None, italic=False, name="Calibri"):
    kwargs = {"name": name, "bold": bold, "italic": italic}
    if color: kwargs["color"] = color
    if size:  kwargs["size"] = size
    return Font(**kwargs)

def align(h="left", v="center", wrap=False):
    return Alignment(horizontal=h, vertical=v, wrap_text=wrap)

def thin_border(color=BORDER_C):
    s = Side(style="thin", color=color)
    return Border(left=s, right=s, top=s, bottom=s)

def medium_border(color=AMBER):
    s = Side(style="medium", color=color)
    return Border(left=s, right=s, top=s, bottom=s)

def bottom_border(color=AMBER):
    return Border(bottom=Side(style="medium", color=color))

# ================================================================
# WORKBOOK
# ================================================================
wb = Workbook()
wb.remove(wb.active)   # remove default Sheet

def add_ws(name):
    ws = wb.create_sheet(title=name)
    ws.sheet_view.showGridLines = False
    ws.sheet_view.showRowColHeaders = False
    return ws

# ================================================================
# SHEET LIST (order matters for Excel tab order)
# ================================================================
SHEET_ORDER = [
    "MENU", "DASHBOARD",
    "FORM_Produit", "FORM_Client", "FORM_Fournisseur", "FORM_Depense",
    "FORM_Vente", "FORM_Achat", "FORM_Mouvement",
    "FORM_Reglement", "FORM_PaiementFournisseur",
    "VIEW_Stock", "VIEW_Ventes", "VIEW_Achats",
    "VIEW_ComptesClients", "VIEW_ComptesFournisseurs", "VIEW_Caisse",
    "GUIDE",
    "Produits", "Clients", "Fournisseurs", "Mouvements_de_stock",
    "Ventes", "Achats", "Depenses", "Reglements",
    "Paiements_fournisseurs", "Caisse",
    "Ref_Categories", "Ref_Formats", "Ref_ModesPaiement",
    "Ref_TypeMouvement", "Ref_ComptesCaisse", "Ref_CategoriesDepenses",
    "Ref_Utilisateurs", "Ref_TypeClient", "Ref_TypeSourceCaisse",
    "PIVOT_Data", "PIVOT_KPI",
]

sheets = {}
for sn in SHEET_ORDER:
    sheets[sn] = add_ws(sn)

print(f"  {len(sheets)} feuilles créées.")

# ================================================================
# HELPERS
# ================================================================
def set_cell(ws, row, col, value=None, bg=None, fg=None, bold=False,
             size=11, italic=False, h="left", v="center", wrap=False,
             num_fmt=None, border=None, font_name="Calibri"):
    cell = ws.cell(row=row, column=col)
    if value is not None:
        cell.value = value
    if bg:
        cell.fill = fill(bg)
    f_args = {"name": font_name, "bold": bold, "italic": italic, "size": size}
    if fg:
        f_args["color"] = fg
    cell.font = Font(**f_args)
    cell.alignment = align(h, v, wrap)
    if num_fmt:
        cell.number_format = num_fmt
    if border:
        cell.border = border
    return cell

def merge(ws, r1, c1, r2, c2, value=None, bg=None, fg=None, bold=False,
          size=11, italic=False, h="center", v="center", wrap=False, num_fmt=None):
    ws.merge_cells(start_row=r1, start_column=c1, end_row=r2, end_column=c2)
    cell = set_cell(ws, r1, c1, value, bg, fg, bold, size, italic, h, v, wrap, num_fmt)
    return cell

def row_height(ws, row, h):
    ws.row_dimensions[row].height = h

def col_width(ws, col, w):
    ws.column_dimensions[get_column_letter(col)].width = w

def banner(ws, title, subtitle, ncols=14, title_row=1, bg=NAVY, fg=WHITE, sub_fg=AMBER):
    # Fill background
    for r in range(title_row, title_row+3):
        for c in range(1, ncols+1):
            ws.cell(row=r, column=c).fill = fill(bg)
    # Amber accent line
    for c in range(1, ncols+1):
        ws.cell(row=title_row+2, column=c).fill = fill(AMBER)
    row_height(ws, title_row+2, 3)
    # Title
    merge(ws, title_row, 1, title_row, ncols, value=title,
          bg=bg, fg=fg, bold=True, size=16, h="center")
    row_height(ws, title_row, 36)
    # Subtitle
    merge(ws, title_row+1, 1, title_row+1, ncols,
          value=subtitle, bg=bg, fg=sub_fg, size=9, italic=True, h="center")
    row_height(ws, title_row+1, 18)

def section_hdr(ws, row, c1, c2, text, bg=SLATE, fg=AMBER):
    for c in range(c1, c2+1):
        ws.cell(row=row, column=c).fill = fill(bg)
    merge(ws, row, c1, row, c2, value="  " + text,
          bg=bg, fg=fg, bold=True, size=10, h="left")
    row_height(ws, row, 22)

def header_row(ws, row, headers, widths, start_col=1, bg=NAVY, fg=WHITE):
    for i, (h, w) in enumerate(zip(headers, widths)):
        col = start_col + i
        set_cell(ws, row, col, h, bg=bg, fg=fg, bold=True, size=9, h="center")
        col_width(ws, col, w)
    row_height(ws, row, 22)

def label_cell(ws, row, label, required=False, bg=SLATE, fg=WHITE):
    lbl = label + " *" if required else label
    set_cell(ws, row, 1, lbl, bg=bg, fg=fg, bold=False, size=10, h="right")
    col_width(ws, 1, 24)
    row_height(ws, row, 24)

def input_cell(ws, row, col=2, num_fmt=None, bg=WHITE, fg=GRAY_DRK):
    c = ws.cell(row=row, column=col)
    c.fill = fill(bg)
    c.font = Font(name="Calibri", color=fg, size=10)
    c.alignment = align("left", "center")
    c.border = medium_border(AMBER)
    if num_fmt:
        c.number_format = num_fmt
    col_width(ws, col, 24)

def form_row(ws, row, label, required=False, num_fmt=None):
    label_cell(ws, row, label, required)
    input_cell(ws, row, 2, num_fmt)
    row_height(ws, row, 24)

def add_dv(ws, list_formula, rows, col):
    """Add dropdown data validation to a column range"""
    dv = DataValidation(type="list", formula1=list_formula, allow_blank=True,
                        showDropDown=False, showErrorMessage=True,
                        errorTitle="Valeur invalide", error="Choisissez dans la liste.")
    dv.sqref = f"{get_column_letter(col)}{rows[0]}:{get_column_letter(col)}{rows[-1]}"
    ws.add_data_validation(dv)

# ================================================================
# REF_* DATA
# ================================================================
print("  Remplissage Ref_*...")
REFS = {
    "Ref_Categories":        {"pre":"C",   "h":["ID_Cat","Libelle"],  "data":["Biere","Eau","Jus","Boisson gazeuse","Boisson energisante","Autre"]},
    "Ref_Formats":           {"pre":"F",   "h":["ID_Fmt","Libelle"],  "data":["Casier","Demi-casier","Palette","Demi-palette","Unite","Bouteille"]},
    "Ref_ModesPaiement":     {"pre":"MP",  "h":["ID_MP","Libelle"],   "data":["Especes","MTN MoMo","Orange Money","Banque","Credit"]},
    "Ref_TypeMouvement":     {"pre":"TM",  "h":["ID_TM","Libelle"],   "data":["Entree","Vente","Casse","Perte","Ajustement","Retour"]},
    "Ref_ComptesCaisse":     {"pre":"CP",  "h":["ID_Cpt","Libelle"],  "data":["Especes","MTN MoMo","Orange Money","Banque"]},
    "Ref_CategoriesDepenses":{"pre":"CD",  "h":["ID_CD","Libelle"],   "data":["Transport","Electricite","Salaires","Reparation","Carburant","Nettoyage","Livraison","Autre"]},
    "Ref_TypeClient":        {"pre":"TC",  "h":["ID_TC","Libelle"],   "data":["Particulier","Revendeur","Restaurant/Bar","Autre"]},
    "Ref_TypeSourceCaisse":  {"pre":"TSC", "h":["ID_TSC","Libelle"],  "data":["Vente","Achat","Depense","Reglement","Paiement fournisseur","Ajustement"]},
}

REF_UTILISATEURS = [("U1","Admin","Administrateur","Oui"),
                    ("U2","Caissier","Caissier","Oui"),
                    ("U3","Vendeur","Vendeur","Oui")]

for ref_name, ref in REFS.items():
    ws = sheets[ref_name]
    pre = ref["pre"]; headers = ref["h"]; data = ref["data"]
    # Banner
    merge(ws, 1, 1, 1, 3, value=ref_name, bg=NAVY, fg=AMBER, bold=True, size=10, h="center")
    row_height(ws, 1, 24)
    # Headers
    for ci, h in enumerate(headers + ["Display"]):
        bg_h = SLATE if ci < len(headers) else AMBER_DRK
        set_cell(ws, 2, ci+1, h, bg=bg_h, fg=WHITE, bold=True, size=9, h="center")
    row_height(ws, 2, 20)
    # Data
    for i, item in enumerate(data):
        r = i + 3
        pk = pre + str(i+1)
        set_cell(ws, r, 1, pk, bg=AMBER_LGT if i%2==0 else WHITE, fg=NAVY, bold=True, size=9)
        set_cell(ws, r, 2, item, bg=AMBER_LGT if i%2==0 else WHITE, fg=GRAY_DRK, size=9)
        set_cell(ws, r, 3, f"{pk} - {item}", bg=AMBER_LGT if i%2==0 else WHITE, fg=SLATE, size=9, italic=True)
        row_height(ws, r, 18)
    col_width(ws, 1, 8); col_width(ws, 2, 20); col_width(ws, 3, 28)

# Ref_Utilisateurs special
ws = sheets["Ref_Utilisateurs"]
merge(ws, 1, 1, 1, 5, value="Ref_Utilisateurs", bg=NAVY, fg=AMBER, bold=True, size=10, h="center")
row_height(ws, 1, 24)
for ci, h in enumerate(["ID_Utl","Nom","Role","Actif","Display"]):
    set_cell(ws, 2, ci+1, h, bg=SLATE, fg=WHITE, bold=True, size=9, h="center")
row_height(ws, 2, 20)
for i, (pk, nom, role, actif) in enumerate(REF_UTILISATEURS):
    r = i+3
    bg_r = AMBER_LGT if i%2==0 else WHITE
    set_cell(ws, r, 1, pk, bg=bg_r, fg=NAVY, bold=True, size=9)
    set_cell(ws, r, 2, nom, bg=bg_r, fg=GRAY_DRK, size=9)
    set_cell(ws, r, 3, role, bg=bg_r, fg=GRAY_DRK, size=9)
    set_cell(ws, r, 4, actif, bg=bg_r, fg=GREEN, bold=True, size=9)
    set_cell(ws, r, 5, f"{pk} - {nom}", bg=bg_r, fg=SLATE, size=9, italic=True)
    row_height(ws, r, 18)
for ci, w in enumerate([8,14,16,8,22]): col_width(ws, ci+1, w)

print("  Ref_* OK.")

# ================================================================
# TABLE SCHEMAS
# ================================================================
print("  Tables de données...")

def table_header(ws, title, subtitle, headers, widths, data_row=3, ncols=None):
    nc = ncols or len(headers)
    banner(ws, title, subtitle, ncols=nc)
    header_row(ws, data_row, headers, widths)
    # Freeze pane below header
    ws.freeze_panes = ws.cell(row=data_row+1, column=1)

# PRODUITS
ws = sheets["Produits"]
h_prod = ["ID Produit","Nom du produit","ID Cat.","Categorie","ID Fmt","Unite princ.",
          "Prix achat","Px Casier/Pal.","Px Demi-fmt","Px Unite/Bout.",
          "Stk initial","Stk min","Stk actuel","Valeur stk","Statut","Fact.casse","ID Fourn.","Fournisseur"]
w_prod = [10,22,8,14,7,12,11,14,12,14,10,8,10,12,9,10,10,18]
table_header(ws, "DEPOT LA CACHETTE — PRODUITS", "Table principale des produits", h_prod, w_prod, ncols=18)
# Sample formula hints in row 4
ws["A4"] = '=IF(B4<>"","P"&TEXT(ROW()-3,"000"),"")'
ws["D4"] = '=IFERROR(XLOOKUP(C4,Ref_Categories!$A:$A,Ref_Categories!$B:$B,"?"),"")'
ws["F4"] = '=IFERROR(XLOOKUP(E4,Ref_Formats!$A:$A,Ref_Formats!$B:$B,"?"),"")'
ws["M4"] = '=IF(A4<>"",K4+IFERROR(SUMPRODUCT((Mouvements_de_stock!$C$4:$C$2000=A4)*Mouvements_de_stock!$J$4:$J$2000),0),"")'
ws["N4"] = '=IFERROR(M4*G4,"")'
ws["O4"] = '=IF(A4="","",IF(IFERROR(M4,0)<=0,"RUPTURE",IF(IFERROR(M4,0)<=L4,"ALERTE","OK")))'
ws["R4"] = '=IFERROR(XLOOKUP(Q4,Fournisseurs!$A$4:$A$500,Fournisseurs!$B$4:$B$500,"?"),"")'
for c in [7,8,9,10,14]: ws.cell(4,c).number_format = "#,##0"
ws["G4"].font = Font(color=GRAY_DRK, italic=True, size=9)

# CLIENTS
ws = sheets["Clients"]
h_cl = ["ID Client","Nom / Entreprise","Telephone","Adresse","ID TypeCl","Type client","Solde du","Observation"]
w_cl = [10,22,14,20,10,14,12,22]
table_header(ws, "DEPOT LA CACHETTE — CLIENTS", "Table clients", h_cl, w_cl, ncols=8)
ws["A4"] = '=IF(B4<>"","CL"&TEXT(ROW()-3,"000"),"")'
ws["F4"] = '=IFERROR(XLOOKUP(E4,Ref_TypeClient!$A:$A,Ref_TypeClient!$B:$B,"?"),"")'
ws["G4"] = '=IF(A4="","",IFERROR(SUMPRODUCT((Ventes!$C$4:$C$2000=A4)*(Ventes!$M$4:$M$2000="MP5")*Ventes!$L$4:$L$2000)-SUMPRODUCT((Reglements!$C$4:$C$2000=A4)*Reglements!$E$4:$E$2000),0))'
ws["G4"].number_format = "#,##0"

# FOURNISSEURS
ws = sheets["Fournisseurs"]
h_fo = ["ID Fourn.","Nom / Entreprise","Telephone","Adresse","Contact","Solde du","Observation"]
w_fo = [12,22,14,20,16,12,22]
table_header(ws, "DEPOT LA CACHETTE — FOURNISSEURS", "Table fournisseurs", h_fo, w_fo, ncols=7)
ws["A4"] = '=IF(B4<>"","FO"&TEXT(ROW()-3,"000"),"")'
ws["F4"] = '=IF(A4="","",IFERROR(SUMPRODUCT((Achats!$C$4:$C$2000=A4)*(Achats!$M$4:$M$2000="MP5")*Achats!$L$4:$L$2000)-SUMPRODUCT((Paiements_fournisseurs!$C$4:$C$2000=A4)*Paiements_fournisseurs!$E$4:$E$2000),0))'
ws["F4"].number_format = "#,##0"

# MOUVEMENTS_DE_STOCK
ws = sheets["Mouvements_de_stock"]
h_mvt = ["ID Mouvement","Date","ID Produit","Produit","ID TM","Type","ID Fmt","Format",
          "Qte saisie","Eq.stock","Prix ref","Val.casse","Reference","ID Utl","Utilisateur","Observation","Statut"]
w_mvt = [13,12,10,18,8,10,8,10,10,9,10,12,14,10,14,20,10]
table_header(ws, "DEPOT LA CACHETTE — MOUVEMENTS DE STOCK", "Tous mouvements de stock", h_mvt, w_mvt, ncols=17)
ws["A4"] = '=IF(C4<>"","M"&TEXT(ROW()-3,"00000"),"")'
ws["D4"] = '=IFERROR(XLOOKUP(C4,Produits!$A$4:$A$500,Produits!$B$4:$B$500,"?"),"")'
ws["F4"] = '=IFERROR(XLOOKUP(E4,Ref_TypeMouvement!$A:$A,Ref_TypeMouvement!$B:$B,"?"),"")'
ws["H4"] = '=IFERROR(XLOOKUP(G4,Ref_Formats!$A:$A,Ref_Formats!$B:$B,"?"),"")'
ws["O4"] = '=IFERROR(XLOOKUP(N4,Ref_Utilisateurs!$A:$A,Ref_Utilisateurs!$B:$B,"?"),"")'

# VENTES
ws = sheets["Ventes"]
h_ven = ["ID Vente","Date","ID Client","Client","ID Produit","Produit","ID Fmt","Format",
          "Quantite","Eq.stock","Prix unit.","Montant","ID Mode","Mode paiement","ID Utl","Utilisateur","Reference","Statut","Ctrl stock"]
w_ven = [10,12,10,18,10,18,8,12,8,8,11,12,9,14,8,14,14,10,12]
table_header(ws, "DEPOT LA CACHETTE — VENTES", "Historique des ventes", h_ven, w_ven, ncols=19)
ws["A4"] = '=IF(C4<>"","V"&TEXT(ROW()-3,"00000"),"")'
ws["D4"] = '=IFERROR(XLOOKUP(C4,Clients!$A$4:$A$500,Clients!$B$4:$B$500,"?"),"")'
ws["F4"] = '=IFERROR(XLOOKUP(E4,Produits!$A$4:$A$500,Produits!$B$4:$B$500,"?"),"")'
ws["H4"] = '=IFERROR(XLOOKUP(G4,Ref_Formats!$A:$A,Ref_Formats!$B:$B,"?"),"")'
ws["L4"] = '=IF(AND(I4<>"",K4<>""),IFERROR(I4*K4,0),"")'
ws["N4"] = '=IFERROR(XLOOKUP(M4,Ref_ModesPaiement!$A:$A,Ref_ModesPaiement!$B:$B,"?"),"")'
ws["P4"] = '=IFERROR(XLOOKUP(O4,Ref_Utilisateurs!$A:$A,Ref_Utilisateurs!$B:$B,"?"),"")'
ws["L4"].number_format = "#,##0"
ws["K4"].number_format = "#,##0"

# ACHATS
ws = sheets["Achats"]
h_ach = ["ID Achat","Date","ID Fourn.","Fournisseur","ID Produit","Produit","ID Fmt","Format",
          "Quantite","Eq.stock","Prix achat","Montant","ID Mode","Mode paiement","ID Utl","Utilisateur","Reference"]
w_ach = [10,12,11,18,10,18,8,12,8,8,11,12,9,14,8,14,14]
table_header(ws, "DEPOT LA CACHETTE — ACHATS", "Historique des achats", h_ach, w_ach, ncols=17)
ws["A4"] = '=IF(C4<>"","A"&TEXT(ROW()-3,"00000"),"")'
ws["D4"] = '=IFERROR(XLOOKUP(C4,Fournisseurs!$A$4:$A$500,Fournisseurs!$B$4:$B$500,"?"),"")'
ws["F4"] = '=IFERROR(XLOOKUP(E4,Produits!$A$4:$A$500,Produits!$B$4:$B$500,"?"),"")'
ws["H4"] = '=IFERROR(XLOOKUP(G4,Ref_Formats!$A:$A,Ref_Formats!$B:$B,"?"),"")'
ws["L4"] = '=IF(AND(I4<>"",K4<>""),IFERROR(I4*K4,0),"")'
ws["N4"] = '=IFERROR(XLOOKUP(M4,Ref_ModesPaiement!$A:$A,Ref_ModesPaiement!$B:$B,"?"),"")'
ws["P4"] = '=IFERROR(XLOOKUP(O4,Ref_Utilisateurs!$A:$A,Ref_Utilisateurs!$B:$B,"?"),"")'
ws["L4"].number_format = "#,##0"

# DEPENSES
ws = sheets["Depenses"]
h_dep = ["ID Depense","Date","ID Cat.","Categorie","Description","Montant",
          "ID Mode","Mode paiement","Beneficiaire","ID Utl","Utilisateur","Reference","Statut"]
w_dep = [11,12,10,14,22,12,9,14,18,10,14,14,10]
table_header(ws, "DEPOT LA CACHETTE — DEPENSES", "Table depenses", h_dep, w_dep, ncols=13)
ws["A4"] = '=IF(C4<>"","DEP"&TEXT(ROW()-3,"00000"),"")'
ws["D4"] = '=IFERROR(XLOOKUP(C4,Ref_CategoriesDepenses!$A:$A,Ref_CategoriesDepenses!$B:$B,"?"),"")'
ws["H4"] = '=IFERROR(XLOOKUP(G4,Ref_ModesPaiement!$A:$A,Ref_ModesPaiement!$B:$B,"?"),"")'
ws["K4"] = '=IFERROR(XLOOKUP(J4,Ref_Utilisateurs!$A:$A,Ref_Utilisateurs!$B:$B,"?"),"")'
ws["F4"].number_format = "#,##0"

# REGLEMENTS
ws = sheets["Reglements"]
h_reg = ["ID Reglement","Date","ID Client","Client","Montant","ID Compte","Compte","Reference","ID Utl","Utilisateur","Observation"]
w_reg = [12,12,10,18,12,10,12,14,10,14,22]
table_header(ws, "DEPOT LA CACHETTE — REGLEMENTS CLIENTS", "Table reglements", h_reg, w_reg, ncols=11)
ws["A4"] = '=IF(C4<>"","REG"&TEXT(ROW()-3,"00000"),"")'
ws["D4"] = '=IFERROR(XLOOKUP(C4,Clients!$A$4:$A$500,Clients!$B$4:$B$500,"?"),"")'
ws["G4"] = '=IFERROR(XLOOKUP(F4,Ref_ComptesCaisse!$A:$A,Ref_ComptesCaisse!$B:$B,"?"),"")'
ws["J4"] = '=IFERROR(XLOOKUP(I4,Ref_Utilisateurs!$A:$A,Ref_Utilisateurs!$B:$B,"?"),"")'
ws["E4"].number_format = "#,##0"

# PAIEMENTS_FOURNISSEURS
ws = sheets["Paiements_fournisseurs"]
h_pf = ["ID Paiement","Date","ID Fourn.","Fournisseur","Montant","ID Compte","Compte","Reference","ID Utl","Utilisateur","Observation"]
w_pf = [12,12,11,18,12,10,12,14,10,14,22]
table_header(ws, "DEPOT LA CACHETTE — PAIEMENTS FOURNISSEURS", "Table paiements fournisseurs", h_pf, w_pf, ncols=11)
ws["A4"] = '=IF(C4<>"","PF"&TEXT(ROW()-3,"00000"),"")'
ws["D4"] = '=IFERROR(XLOOKUP(C4,Fournisseurs!$A$4:$A$500,Fournisseurs!$B$4:$B$500,"?"),"")'
ws["G4"] = '=IFERROR(XLOOKUP(F4,Ref_ComptesCaisse!$A:$A,Ref_ComptesCaisse!$B:$B,"?"),"")'
ws["J4"] = '=IFERROR(XLOOKUP(I4,Ref_Utilisateurs!$A:$A,Ref_Utilisateurs!$B:$B,"?"),"")'
ws["E4"].number_format = "#,##0"

# CAISSE
ws = sheets["Caisse"]
h_ca = ["ID Operation","Date","ID TypeSrc","Type source","ID Source","Ref.source",
        "Description","ID Compte","Compte","Entree","Sortie","Solde cpt","ID Utl","Utilisateur","Observation","Statut"]
w_ca = [13,14,11,16,12,14,22,10,12,12,12,12,10,14,22,10]
table_header(ws, "DEPOT LA CACHETTE — CAISSE / TRESORERIE", "Mouvements de tresorerie", h_ca, w_ca, ncols=16)
ws["A4"] = '=IF(OR(D4<>"",H4<>""),"OP"&TEXT(ROW()-3,"00000"),"")'
ws["D4"] = '=IFERROR(XLOOKUP(C4,Ref_TypeSourceCaisse!$A:$A,Ref_TypeSourceCaisse!$B:$B,"?"),"")'
ws["I4"] = '=IFERROR(XLOOKUP(H4,Ref_ComptesCaisse!$A:$A,Ref_ComptesCaisse!$B:$B,"?"),"")'
ws["N4"] = '=IFERROR(XLOOKUP(M4,Ref_Utilisateurs!$A:$A,Ref_Utilisateurs!$B:$B,"?"),"")'
for c in [10,11,12]: ws.cell(4,c).number_format = "#,##0"

print("  Tables OK.")

# ================================================================
# MENU
# ================================================================
print("  MENU...")
ws = sheets["MENU"]

# Background
for r in range(1, 30):
    for c in range(1, 17):
        ws.cell(row=r, column=c).fill = fill(NAVY)

# Logo area
for c in range(1, 17):
    ws.cell(row=1, column=c).fill = fill(NAVY)
row_height(ws, 1, 6)
row_height(ws, 2, 60)

# Try insert logo
try:
    if os.path.exists(LOGO_PATH):
        img = XLImage(LOGO_PATH)
        img.width = 400; img.height = 58
        img.anchor = "B2"
        ws.add_image(img)
        merge(ws, 2, 8, 2, 16, value="", bg=NAVY)
    else:
        raise FileNotFoundError
except Exception:
    merge(ws, 2, 1, 2, 16, value="DEPOT LA CACHETTE — GESTION DE STOCK",
          bg=NAVY, fg=AMBER, bold=True, size=20, h="center")

# Version line
merge(ws, 3, 1, 3, 16, value=f"  {VERSION}  |  Systeme de gestion relationnelle",
      bg=NAVY, fg=GRAY_MID, size=9, italic=True, h="center")
row_height(ws, 3, 18)
# Amber divider
merge(ws, 4, 1, 4, 16, bg=AMBER)
row_height(ws, 4, 3)

row_height(ws, 5, 10)

# Section: Saisie
section_hdr(ws, 6, 2, 15, "SAISIE & ENREGISTREMENT", bg=SLATE, fg=AMBER)
row_height(ws, 7, 6)

# Button definitions: [label, row, col_start, col_span, bg, fg]
BTN_SAISIE = [
    ("+ Produit",        8, 2, 2, AMBER,      NAVY),
    ("+ Client",         8, 5, 2, AMBER,      NAVY),
    ("+ Fournisseur",    8, 8, 2, AMBER,      NAVY),
    ("Nouvelle Vente",   8, 11, 2, AMBER,     NAVY),
    ("Nouvel Achat",     8, 14, 2, AMBER,     NAVY),
    ("Mouvement Stock",  9, 2, 2, SLATE_LGT,  WHITE),
    ("Depense",          9, 5, 2, SLATE_LGT,  WHITE),
    ("Reglement Client", 9, 8, 2, SLATE_LGT,  WHITE),
    ("Paiement Fourn.", 9, 11, 2, SLATE_LGT,  WHITE),
]

for lbl, brow, bcol, bspan, bbg, bfg in BTN_SAISIE:
    # Simulate button as merged colored cell
    merge(ws, brow, bcol, brow, bcol+bspan-1, value=lbl,
          bg=bbg, fg=bfg, bold=True, size=10, h="center")
    for c in range(bcol, bcol+bspan):
        ws.cell(brow, c).border = thin_border(NAVY_MID)
    row_height(ws, brow, 34)

row_height(ws, 10, 10)

# Section: Consultation
section_hdr(ws, 11, 2, 15, "CONSULTATION & RAPPORTS", bg=NAVY_MID, fg=AMBER)
row_height(ws, 12, 6)

BTN_CONSULT = [
    ("Stock",            13, 2, 2, TEAL,      WHITE),
    ("Ventes",           13, 5, 2, TEAL,      WHITE),
    ("Achats",           13, 8, 2, TEAL,      WHITE),
    ("Comptes Clients",  13, 11, 2, TEAL,     WHITE),
    ("Comptes Fourn.",   13, 14, 2, TEAL,     WHITE),
]
for lbl, brow, bcol, bspan, bbg, bfg in BTN_CONSULT:
    merge(ws, brow, bcol, brow, bcol+bspan-1, value=lbl,
          bg=bbg, fg=bfg, bold=True, size=10, h="center")
    row_height(ws, brow, 34)

row_height(ws, 14, 10)

# Section: Outils
section_hdr(ws, 15, 2, 15, "OUTILS", bg="0A1520", fg=AMBER)
row_height(ws, 16, 6)

BTN_OUTILS = [
    ("Tableau de Bord",  17, 2, 2, TEAL,   WHITE),
    ("Caisse",           17, 5, 2, PURPLE, WHITE),
    ("Guide d emploi",   17, 8, 2, GRAY_MID,WHITE),
    ("Sauvegarder",      17, 11, 2, GREEN,  WHITE),
]
for lbl, brow, bcol, bspan, bbg, bfg in BTN_OUTILS:
    merge(ws, brow, bcol, brow, bcol+bspan-1, value=lbl,
          bg=bbg, fg=bfg, bold=True, size=10, h="center")
    row_height(ws, brow, 34)

# Footer
for c in range(1,17): ws.cell(18,c).fill = fill(AMBER)
row_height(ws, 18, 2)
merge(ws, 19, 1, 19, 16,
      value="Toutes les saisies passent par les formulaires — les tables de donnees sont protegees.",
      bg=NAVY, fg=GRAY_MID, size=8, italic=True, h="center")
row_height(ws, 19, 16)

# Column widths menu
for c in range(1, 17):
    col_width(ws, c, 9.5)
col_width(ws, 1, 3)

print("  MENU OK.")

# ================================================================
# DASHBOARD
# ================================================================
print("  DASHBOARD...")
ws = sheets["DASHBOARD"]
for r in range(1, 40):
    for c in range(1, 15):
        ws.cell(r,c).fill = fill("111E2B")  # slightly different navy

banner(ws, "DEPOT LA CACHETTE — TABLEAU DE BORD",
       "Indicateurs en temps reel  |  Actualiser pour rafraichir", ncols=14)
try:
    if os.path.exists(LOGO_PATH):
        img2 = XLImage(LOGO_PATH)
        img2.width = 320; img2.height = 46
        img2.anchor = "B1"
        ws.add_image(img2)
except:
    pass

row_height(ws, 3, 3)
row_height(ws, 4, 8)

# KPI cards
KPI = [
    ("VENTES DU JOUR",    TEAL,   2, 4,  "0"),
    ("TRESORERIE TOTALE", GREEN,  5, 7,  "0"),
    ("ALERTES STOCK",     ORANGE, 8, 10, "0"),
    ("CREANCES CLIENTS",  RED,    11,13, "0"),
]
for label, color, c1, c2, val in KPI:
    # Top accent bar
    for c in range(c1, c2+1):
        ws.cell(5, c).fill = fill(color)
    row_height(ws, 5, 5)
    # Card bg
    for r in range(6, 10):
        for c in range(c1, c2+1):
            ws.cell(r, c).fill = fill(NAVY_MID)
    # Label
    merge(ws, 6, c1, 6, c2, value=label, bg=NAVY_MID, fg=color, bold=True, size=8, h="center")
    row_height(ws, 6, 18)
    # Value
    merge(ws, 7, c1, 7, c2, value=0, bg=NAVY_MID, fg=WHITE, bold=True, size=22, h="center")
    ws.cell(7, c1).number_format = "#,##0"
    row_height(ws, 7, 44)
    # Unit
    merge(ws, 8, c1, c2, 8, value="FCFA", bg=NAVY_MID, fg=GRAY_MID, size=8, italic=True, h="center")
    row_height(ws, 8, 14)
    row_height(ws, 9, 8)

row_height(ws, 10, 8)

# Data sections
section_hdr(ws, 11, 2, 13, "APERCU DES DONNEES — TOP PRODUITS & ALERTES STOCK", bg=SLATE, fg=AMBER)
row_height(ws, 11, 20)

# Sub headers
merge(ws, 12, 2, 12, 7, value="TOP PRODUITS (stock actuel)", bg=NAVY, fg=AMBER, bold=True, size=9, h="center")
merge(ws, 12, 8, 12, 13, value="ALERTES STOCK CRITIQUE", bg=NAVY, fg=RED, bold=True, size=9, h="center")
row_height(ws, 12, 20)

for i, h in enumerate(["Produit","Stock","Valeur"]):
    c = 2 + i*2
    merge(ws, 13, c, 13, c+1, value=h, bg=SLATE, fg=WHITE, bold=True, size=8, h="center")
for i, h in enumerate(["Produit","Stock","Statut"]):
    c = 8 + i*2
    merge(ws, 13, c, 13, c+1, value=h, bg=SLATE, fg=WHITE, bold=True, size=8, h="center")
row_height(ws, 13, 18)

for r in range(14, 24):
    bg_r = ROW_ALT if r%2==0 else WHITE
    for c in range(2, 14):
        ws.cell(r,c).fill = fill(bg_r)
    row_height(ws, r, 18)

# Buttons
row_height(ws, 24, 8)
set_cell(ws, 25, 2, "< Retour Menu", bg=AMBER, fg=NAVY, bold=True, size=9, h="center")
set_cell(ws, 25, 4, "Actualiser", bg=TEAL, fg=WHITE, bold=True, size=9, h="center")
row_height(ws, 25, 26)

for c in range(1, 15): col_width(ws, c, 9.0)

print("  DASHBOARD OK.")

# ================================================================
# FORM SHEETS
# ================================================================
print("  Formulaires...")

def setup_form(ws_name, title, subtitle, ncols=6):
    ws = sheets[ws_name]
    for r in range(1, 25):
        for c in range(1, ncols+3):
            ws.cell(r,c).fill = fill(SLATE)
    banner(ws, title, subtitle, ncols=ncols+2, bg=NAVY, fg=WHITE, sub_fg=AMBER)
    # Back button (text label)
    set_cell(ws, 2, ncols, "< Retour Menu", bg=AMBER_DRK, fg=WHITE, bold=True, size=8, h="center")
    row_height(ws, 3, 8)
    col_width(ws, 1, 24)
    col_width(ws, 2, 26)
    col_width(ws, 3, 20)
    col_width(ws, 4, 20)
    return ws

def form_save_btn(ws, row, save_label="Enregistrer", clear_label="Vider"):
    row_height(ws, row, 34)
    merge(ws, row, 2, row, 3, value=save_label, bg=AMBER, fg=NAVY, bold=True, size=11, h="center")
    set_cell(ws, row, 4, clear_label, bg=SLATE_LGT, fg=WHITE, bold=True, size=9, h="center")

# --- FORM_Produit ---
ws = setup_form("FORM_Produit", "NOUVEAU PRODUIT", "Enregistrer un produit au catalogue")
section_hdr(ws, 4, 1, 6, "INFORMATIONS PRODUIT")
PROD_ROWS = [
    (5, "Nom du produit", True, None),
    (6, "Categorie *", True, None),
    (7, "Unite principale", False, None),
    (8, "Prix achat (FCFA)", True, "#,##0"),
    (9, "Prix Casier / Palette", False, "#,##0"),
    (10,"Prix Demi-format", False, "#,##0"),
    (11,"Prix Unite / Bouteille", False, "#,##0"),
    (12,"Stock initial", False, "0"),
    (13,"Stock minimum (alerte)", False, "0"),
    (14,"Facteur casse", False, "0"),
    (15,"Fournisseur principal", False, None),
]
for row, lbl, req, fmt in PROD_ROWS:
    label_cell(ws, row, lbl, req)
    input_cell(ws, row, 2, fmt)
ws.cell(12,2).value = 0; ws.cell(13,2).value = 0; ws.cell(14,2).value = 1
row_height(ws, 16, 12)
form_save_btn(ws, 17)

# Dropdowns
add_dv(ws, "=Ref_Categories!$C$3:$C$10", [6], 2)
add_dv(ws, "=Ref_Formats!$C$3:$C$8", [7], 2)

# --- FORM_Client ---
ws = setup_form("FORM_Client", "NOUVEAU CLIENT", "Enregistrer un client")
section_hdr(ws, 4, 1, 6, "INFORMATIONS CLIENT")
for row, lbl, req, fmt in [
    (5,"Nom / Entreprise",True,None),(6,"Telephone",False,None),
    (7,"Adresse",False,None),(8,"Type client",False,None),(9,"Observation",False,None)]:
    label_cell(ws, row, lbl, req); input_cell(ws, row, 2, fmt)
row_height(ws, 10, 12)
form_save_btn(ws, 11)
add_dv(ws, "=Ref_TypeClient!$C$3:$C$6", [8], 2)

# --- FORM_Fournisseur ---
ws = setup_form("FORM_Fournisseur", "NOUVEAU FOURNISSEUR", "Enregistrer un fournisseur")
section_hdr(ws, 4, 1, 6, "INFORMATIONS FOURNISSEUR")
for row, lbl, req, fmt in [
    (5,"Nom / Entreprise",True,None),(6,"Telephone",False,None),
    (7,"Adresse",False,None),(8,"Contact",False,None),(9,"Observation",False,None)]:
    label_cell(ws, row, lbl, req); input_cell(ws, row, 2, fmt)
row_height(ws, 10, 12)
form_save_btn(ws, 11)

# --- FORM_Vente ---
ws = setup_form("FORM_Vente", "NOUVELLE VENTE", "Enregistrer une vente", ncols=7)
section_hdr(ws, 4, 1, 7, "SAISIE DE VENTE")
VENTE_ROWS = [
    (5,"Date *",True,"DD/MM/YYYY"),(6,"Client (ID)",False,None),
    (7,"Produit (ID) *",True,None),(8,"Format vendu",False,None),
    (9,"Quantite *",True,None),(10,"Mode de paiement *",True,None),
    (11,"Utilisateur *",True,None),(12,"Reference",False,None),
]
for row, lbl, req, fmt in VENTE_ROWS:
    label_cell(ws, row, lbl, req); input_cell(ws, row, 2, fmt)
ws.cell(5,2).value = "=TODAY()"; ws.cell(5,2).number_format = "DD/MM/YYYY"
# Display labels
set_cell(ws, 6, 3, "→ Nom client s'affichera ici", bg=SLATE, fg=AMBER, italic=True, size=8)
set_cell(ws, 7, 3, "→ Nom produit s'affichera ici", bg=SLATE, fg=AMBER, italic=True, size=8)
set_cell(ws, 9, 3, "Stock dispo : voir VIEW_Stock", bg=SLATE, fg=GREEN, bold=True, size=8)
# Montant estimé
row_height(ws, 13, 28)
label_cell(ws, 13, "Montant estime")
set_cell(ws, 13, 2, "Calcul auto a l'enregistrement", bg=NAVY, fg=AMBER, bold=True, size=10, h="center")
ws.cell(13,2).border = medium_border(AMBER)
row_height(ws, 14, 12)
form_save_btn(ws, 15)
add_dv(ws, "=Clients!$A$4:$A$203", [6], 2)
add_dv(ws, "=Produits!$A$4:$A$503", [7], 2)
add_dv(ws, "=Ref_Formats!$C$3:$C$8", [8], 2)
add_dv(ws, "=Ref_ModesPaiement!$C$3:$C$7", [10], 2)
add_dv(ws, "=Ref_Utilisateurs!$C$3:$C$5", [11], 2)

# --- FORM_Achat ---
ws = setup_form("FORM_Achat", "NOUVEL ACHAT", "Enregistrer un achat fournisseur", ncols=7)
section_hdr(ws, 4, 1, 7, "SAISIE D'ACHAT")
ACHAT_ROWS = [
    (5,"Date *",True,"DD/MM/YYYY"),(6,"Fournisseur (ID) *",True,None),
    (7,"Produit (ID) *",True,None),(8,"Format recu",False,None),
    (9,"Quantite *",True,None),(10,"Prix achat (FCFA) *",True,"#,##0"),
    (11,"Mode de paiement *",True,None),(12,"Utilisateur *",True,None),
    (13,"Reference",False,None),
]
for row, lbl, req, fmt in ACHAT_ROWS:
    label_cell(ws, row, lbl, req); input_cell(ws, row, 2, fmt)
ws.cell(5,2).value = "=TODAY()"; ws.cell(5,2).number_format = "DD/MM/YYYY"
set_cell(ws, 6, 3, "→ Nom fournisseur s'affichera ici", bg=SLATE, fg=AMBER, italic=True, size=8)
set_cell(ws, 7, 3, "→ Nom produit s'affichera ici", bg=SLATE, fg=AMBER, italic=True, size=8)
row_height(ws, 14, 26)
label_cell(ws, 14, "Montant total")
set_cell(ws, 14, 2, "= Quantite x Prix achat", bg=NAVY, fg=AMBER, bold=True, size=10, h="center")
ws.cell(14,2).border = medium_border(AMBER)
row_height(ws, 15, 12)
form_save_btn(ws, 16)
add_dv(ws, "=Fournisseurs!$A$4:$A$203", [6], 2)
add_dv(ws, "=Produits!$A$4:$A$503", [7], 2)
add_dv(ws, "=Ref_Formats!$C$3:$C$8", [8], 2)
add_dv(ws, "=Ref_ModesPaiement!$C$3:$C$7", [11], 2)
add_dv(ws, "=Ref_Utilisateurs!$C$3:$C$5", [12], 2)

# --- FORM_Mouvement ---
ws = setup_form("FORM_Mouvement", "MOUVEMENT DE STOCK", "Entree, casse, perte, ajustement")
section_hdr(ws, 4, 1, 6, "SAISIE DE MOUVEMENT")
MVT_ROWS = [
    (5,"Date *",True,"DD/MM/YYYY"),(6,"Produit (ID) *",True,None),
    (7,"Type de mouvement *",True,None),(8,"Format",False,None),
    (9,"Quantite *",True,None),(10,"Prix de reference",False,"#,##0"),
    (11,"Reference",False,None),(12,"Utilisateur *",True,None),(13,"Observation",False,None),
]
for row, lbl, req, fmt in MVT_ROWS:
    label_cell(ws, row, lbl, req); input_cell(ws, row, 2, fmt)
ws.cell(5,2).value = "=TODAY()"; ws.cell(5,2).number_format = "DD/MM/YYYY"
row_height(ws, 14, 12)
form_save_btn(ws, 15)
add_dv(ws, "=Produits!$A$4:$A$503", [6], 2)
add_dv(ws, "=Ref_TypeMouvement!$C$3:$C$8", [7], 2)
add_dv(ws, "=Ref_Formats!$C$3:$C$8", [8], 2)
add_dv(ws, "=Ref_Utilisateurs!$C$3:$C$5", [12], 2)

# --- FORM_Depense ---
ws = setup_form("FORM_Depense", "NOUVELLE DEPENSE", "Enregistrer une depense")
section_hdr(ws, 4, 1, 6, "SAISIE DE DEPENSE")
DEP_ROWS = [
    (5,"Date *",True,"DD/MM/YYYY"),(6,"Categorie *",True,None),
    (7,"Description",False,None),(8,"Montant (FCFA) *",True,"#,##0"),
    (9,"Mode de paiement *",True,None),(10,"Beneficiaire",False,None),
    (11,"Utilisateur *",True,None),(12,"Reference",False,None),
]
for row, lbl, req, fmt in DEP_ROWS:
    label_cell(ws, row, lbl, req); input_cell(ws, row, 2, fmt)
ws.cell(5,2).value = "=TODAY()"; ws.cell(5,2).number_format = "DD/MM/YYYY"
row_height(ws, 13, 12)
form_save_btn(ws, 14)
add_dv(ws, "=Ref_CategoriesDepenses!$C$3:$C$10", [6], 2)
add_dv(ws, "=Ref_ModesPaiement!$C$3:$C$7", [9], 2)
add_dv(ws, "=Ref_Utilisateurs!$C$3:$C$5", [11], 2)

# --- FORM_Reglement ---
ws = setup_form("FORM_Reglement", "REGLEMENT CLIENT", "Encaisser un paiement client")
section_hdr(ws, 4, 1, 6, "SAISIE DE REGLEMENT")
REG_ROWS = [
    (5,"Date *",True,"DD/MM/YYYY"),(6,"Client (ID) *",True,None),
    (7,"Montant (FCFA) *",True,"#,##0"),(8,"Compte encaissement *",True,None),
    (9,"Reference",False,None),(10,"Utilisateur *",True,None),(11,"Observation",False,None),
]
for row, lbl, req, fmt in REG_ROWS:
    label_cell(ws, row, lbl, req); input_cell(ws, row, 2, fmt)
ws.cell(5,2).value = "=TODAY()"; ws.cell(5,2).number_format = "DD/MM/YYYY"
set_cell(ws, 6, 3, "→ Nom + Solde du client ici", bg=SLATE, fg=AMBER, italic=True, size=8)
row_height(ws, 12, 12)
form_save_btn(ws, 13)
add_dv(ws, "=Clients!$A$4:$A$203", [6], 2)
add_dv(ws, "=Ref_ComptesCaisse!$C$3:$C$6", [8], 2)
add_dv(ws, "=Ref_Utilisateurs!$C$3:$C$5", [10], 2)

# --- FORM_PaiementFournisseur ---
ws = setup_form("FORM_PaiementFournisseur", "PAIEMENT FOURNISSEUR", "Regler un fournisseur")
section_hdr(ws, 4, 1, 6, "SAISIE DE PAIEMENT FOURNISSEUR")
PF_ROWS = [
    (5,"Date *",True,"DD/MM/YYYY"),(6,"Fournisseur (ID) *",True,None),
    (7,"Montant (FCFA) *",True,"#,##0"),(8,"Compte paiement *",True,None),
    (9,"Reference",False,None),(10,"Utilisateur *",True,None),(11,"Observation",False,None),
]
for row, lbl, req, fmt in PF_ROWS:
    label_cell(ws, row, lbl, req); input_cell(ws, row, 2, fmt)
ws.cell(5,2).value = "=TODAY()"; ws.cell(5,2).number_format = "DD/MM/YYYY"
set_cell(ws, 6, 3, "→ Nom + Solde du fournisseur ici", bg=SLATE, fg=AMBER, italic=True, size=8)
row_height(ws, 12, 12)
form_save_btn(ws, 13)
add_dv(ws, "=Fournisseurs!$A$4:$A$203", [6], 2)
add_dv(ws, "=Ref_ComptesCaisse!$C$3:$C$6", [8], 2)
add_dv(ws, "=Ref_Utilisateurs!$C$3:$C$5", [10], 2)

print("  Formulaires OK.")

# ================================================================
# VIEW SHEETS (read-only consultation)
# ================================================================
print("  Vues de consultation...")

def view_sheet(ws_name, title, subtitle, headers, widths, ref_sheet, src_cols, n=200):
    ws = sheets[ws_name]
    nc = len(headers)
    banner(ws, title, subtitle, ncols=nc+1)
    # Filter notice
    section_hdr(ws, 3, 1, nc, "  Lecture seule — Utilisez les filtres Excel (Donnees > Filtrer)", bg="1A2B3C", fg=GRAY_MID)
    row_height(ws, 3, 16)
    header_row(ws, 4, headers, widths)
    ws.freeze_panes = ws.cell(5, 1)
    # Data via reference formulas
    for r in range(5, 5+n):
        src_r = r - 5 + 4
        bg_r = ROW_ALT if r%2==0 else WHITE
        for ci, col_letter in enumerate(src_cols):
            c = ws.cell(r, ci+1)
            c.value = f"=IFERROR(INDEX({ref_sheet}!${col_letter}$4:${col_letter}$2000,{r-4}),\"\")"
            c.fill = fill(bg_r)
            c.font = Font(name="Calibri", color=GRAY_DRK, size=9)
            c.alignment = align("left","center")
        row_height(ws, r, 17)
    # Back button
    set_cell(ws, 5+n+1, 2, "< Retour Menu", bg=AMBER, fg=NAVY, bold=True, size=9, h="center")
    row_height(ws, 5+n+1, 26)

view_sheet("VIEW_Stock", "STOCK DES PRODUITS", "Inventaire en temps reel",
    ["ID","Nom Produit","Categorie","Unite","Px Achat","Px Casier","Px Demi","Px Unite",
     "Stk Init","Stk Min","Stk Actuel","Valeur","Statut","Fournisseur"],
    [8,22,14,10,10,10,10,10,8,8,10,12,9,18],
    "Produits", ["A","B","D","F","G","H","I","J","K","L","M","N","O","R"])

view_sheet("VIEW_Ventes", "HISTORIQUE DES VENTES", "Toutes les ventes",
    ["ID Vente","Date","Client","Produit","Format","Qte","Px Unit.","Montant","Mode Paiem.","Utilisateur","Reference","Statut"],
    [10,12,18,18,10,7,10,12,14,12,12,10],
    "Ventes", ["A","B","D","F","H","I","K","L","N","P","Q","R"])

view_sheet("VIEW_Achats", "HISTORIQUE DES ACHATS", "Tous les achats",
    ["ID Achat","Date","Fournisseur","Produit","Format","Qte","Px Achat","Montant","Mode Paiem.","Utilisateur","Reference"],
    [10,12,18,18,10,7,10,12,14,12,12],
    "Achats", ["A","B","D","F","H","I","K","L","N","P","Q"])

view_sheet("VIEW_ComptesClients", "COMPTES CLIENTS", "Soldes et creances",
    ["ID Client","Nom/Entreprise","Telephone","Adresse","Type","Solde Du","Observation"],
    [10,22,14,20,14,12,22],
    "Clients", ["A","B","C","D","F","G","H"])

view_sheet("VIEW_ComptesFournisseurs", "COMPTES FOURNISSEURS", "Soldes fournisseurs",
    ["ID Fourn.","Nom/Entreprise","Telephone","Adresse","Contact","Solde Du","Observation"],
    [10,22,14,20,16,12,22],
    "Fournisseurs", ["A","B","C","D","E","F","G"])

view_sheet("VIEW_Caisse", "CAISSE / TRESORERIE", "Mouvements de tresorerie",
    ["ID Op.","Date","Type Source","Ref.","Description","Compte","Entree","Sortie","Solde","Utilisateur","Observation"],
    [11,14,16,14,22,12,12,12,12,14,20],
    "Caisse", ["A","B","D","F","G","I","J","K","L","N","O"])

print("  Vues OK.")

# ================================================================
# GUIDE
# ================================================================
print("  Guide...")
ws = sheets["GUIDE"]
for r in range(1, 55):
    for c in range(1, 9):
        ws.cell(r,c).fill = fill(BLUE_LGT)

banner(ws, "DEPOT LA CACHETTE — GUIDE D'UTILISATION", "Mode d'emploi complet V4", ncols=8)
set_cell(ws, 2, 8, "< Retour Menu", bg=AMBER_DRK, fg=WHITE, bold=True, size=8, h="center")
row_height(ws, 3, 8)
col_width(ws, 1, 2); col_width(ws, 2, 70)

GUIDE = [
    ("NAVIGATION", True),
    ("Utilisez les boutons colores du MENU pour naviguer entre les sections.", False),
    ("Chaque formulaire a un bouton 'Retour Menu' en haut a droite.", False),
    ("", False),
    ("SAISIE DES DONNEES", True),
    ("Produits : Formulaire FORM_Produit → Enregistrer. ID genere automatiquement.", False),
    ("Clients / Fournisseurs : Meme principe via leurs formulaires dedies.", False),
    ("Ventes : Saisir Client ID, Produit ID, Format, Quantite, Mode paiement.", False),
    ("  → La caisse est mise a jour automatiquement (sauf Credit).", False),
    ("  → Le stock est verifie avant enregistrement (refus si insuffisant).", False),
    ("Achats : Fournisseur, Produit, Quantite, Prix → mouvement Entree auto.", False),
    ("Depenses : Categorie, Montant, Mode → impact caisse immediat.", False),
    ("Reglements : Le client paie sa dette → solde mis a jour.", False),
    ("Paiements Fournisseur : Vous reglez votre dette → solde mis a jour.", False),
    ("", False),
    ("CONSULTATION", True),
    ("VIEW_Stock : Inventaire temps reel. RUPTURE=rouge, ALERTE=orange, OK=vert.", False),
    ("VIEW_Ventes / Achats : Historiques avec filtres Excel integres.", False),
    ("VIEW_ComptesClients / Fournisseurs : Soldes dus, filtrables.", False),
    ("VIEW_Caisse : Tous les mouvements par compte, soldes calcules.", False),
    ("", False),
    ("ARCHITECTURE & SECURITE", True),
    ("Tables de donnees : masquees, protegees (MDP: cachette).", False),
    ("Referentiels Ref_* : tres caches (VeryHidden) — modification via Admin.", False),
    ("Tout passe par VBA → validation → ecriture protegee dans les tables.", False),
    ("Sauvegarde auto a chaque fermeture dans sous-dossier Sauvegardes/.", False),
    ("", False),
    ("IDENTIFIANTS REFERENCES", True),
    ("P001=Produit  CL001=Client  FO001=Fournisseur  V00001=Vente", False),
    ("A00001=Achat  DEP00001=Depense  REG00001=Reglement  PF00001=Paiement Fourn.", False),
    ("M00001=Mouvement  OP00001=Operation caisse", False),
    ("C1..C6=Categories  F1..F6=Formats  MP1..MP5=Modes  U1..U3=Utilisateurs", False),
]

row = 4
for text, is_hdr in GUIDE:
    if is_hdr:
        for c in range(1,9): ws.cell(row,c).fill = fill(NAVY)
        merge(ws, row, 1, row, 8, value="  "+text, bg=NAVY, fg=AMBER, bold=True, size=10, h="left")
        row_height(ws, row, 22)
    elif text == "":
        row_height(ws, row, 7)
        for c in range(1,9): ws.cell(row,c).fill = fill(BLUE_LGT)
    else:
        bg_g = ROW_ALT if row%2==0 else WHITE
        for c in range(1,9): ws.cell(row,c).fill = fill(bg_g)
        merge(ws, row, 1, row, 8, value=text, bg=bg_g, fg=GRAY_DRK, size=9, h="left")
        ws.cell(row,1).alignment = Alignment(horizontal="left", vertical="center", indent=1)
        row_height(ws, row, 17)
    row += 1

print("  Guide OK.")

# ================================================================
# SAVE AS .xlsm  (openpyxl saves as xlsm if macro_enabled=True)
# ================================================================
print("  Sauvegarde...")
wb.template = False
# Save with xlsm extension — openpyxl can write xlsm containers
wb.save(FICHIER_OUT)
print(f"\n✅ FICHIER SAUVEGARDE : {FICHIER_OUT}")
size_mb = os.path.getsize(FICHIER_OUT) / 1024 / 1024
print(f"   Taille : {size_mb:.2f} Mo")
print(f"   Feuilles : {len(wb.sheetnames)}")
print()
print("="*60)
print("ETAPE SUIVANTE — INJECTION VBA (5 minutes)")
print("="*60)
print("1. Ouvrez Depot_La_Cachette_V4.xlsm dans Excel")
print("2. Alt+F11 pour ouvrir l'editeur VBA")
print("3. Fichier > Importer le fichier...")
print(f"   → Importer : VBA_Cachette.bas")
print("4. Fermez l'editeur VBA")
print("5. Sauvegardez (Ctrl+S)")
print("="*60)
