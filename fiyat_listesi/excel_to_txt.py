"""
Excel fiyat listelerini metne çevirir.
Kullanım:  python excel_to_txt.py
(Bu dosyanın bulunduğu klasördeki tüm .xlsx dosyalarını işler)

Çıktılar (aynı klasöre):
  - sari_satirlar.txt  : SADECE sarı işaretli satırlar (yeni paketler) + başlıklar
  - <dosya>__<sayfa>.csv : her sayfanın tam CSV hali (yedek / kontrol için)
Gerekli paket: pip install openpyxl
"""
import csv
import glob
import os

import openpyxl

BASE = os.path.dirname(os.path.abspath(__file__))
YELLOWS = {"FFFFFF00", "00FFFF00", "FFFFFF66", "FFFFEB9C", "FFFFFF99", "FFFFC000"}


def is_yellow(cell):
    try:
        f = cell.fill
        if not f or not f.fill_type:
            return False
        rgb = str(f.fgColor.rgb or "").upper()
        if rgb in YELLOWS or rgb.endswith("FFFF00"):
            return True
        # Tema rengi / indexed sarı (index 5 / 13 = sarı)
        if f.fgColor.type == "indexed" and f.fgColor.indexed in (5, 13):
            return True
    except Exception:
        pass
    return False


def clean(v):
    if v is None:
        return ""
    return str(v).replace("\n", " ").replace("\t", " ").strip()


def main():
    files = sorted(glob.glob(os.path.join(BASE, "*.xlsx")))
    if not files:
        print("Klasörde .xlsx dosyası bulunamadı:", BASE)
        return

    out_path = os.path.join(BASE, "sari_satirlar.txt")
    total_yellow = 0
    with open(out_path, "w", encoding="utf-8") as out:
        for path in files:
            name = os.path.splitext(os.path.basename(path))[0]
            print("İşleniyor:", name)
            wb = openpyxl.load_workbook(path, data_only=True)
            for ws in wb.worksheets:
                header = None
                yellow_rows = []
                csv_name = f"{name[:40]}__{ws.title[:30]}.csv".replace("/", "_")
                with open(os.path.join(BASE, csv_name), "w", newline="", encoding="utf-8-sig") as cf:
                    w = csv.writer(cf)
                    for row in ws.iter_rows():
                        vals = [clean(c.value) for c in row]
                        if not any(vals):
                            continue
                        w.writerow(vals)
                        if header is None:
                            header = vals
                        if any(is_yellow(c) for c in row):
                            yellow_rows.append((row[0].row, vals))

                if yellow_rows:
                    out.write(f"\n===== DOSYA: {name} | SAYFA: {ws.title} | {len(yellow_rows)} sarı satır =====\n")
                    out.write("BASLIK\t" + "\t".join(header or []) + "\n")
                    for rnum, vals in yellow_rows:
                        out.write(f"SATIR {rnum}\t" + "\t".join(vals).rstrip("\t") + "\n")
                    total_yellow += len(yellow_rows)

    print(f"\nBitti. Toplam {total_yellow} sarı satır -> {out_path}")


if __name__ == "__main__":
    main()
