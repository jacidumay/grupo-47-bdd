import pandas as pd

def excel_a_csv(nombre_archivo_excel):
    # Cargar el archivo Excel con todas sus hojas
    excel = pd.ExcelFile(nombre_archivo_excel)
    
    # Iterar sobre cada hoja en el archivo
    for sheet_name in excel.sheet_names:
        # Leer la hoja actual en un DataFrame
        df = pd.read_excel(excel, sheet_name=sheet_name)
        
        # Crear el nombre del archivo CSV basado en el nombre de la hoja
        nombre_archivo_csv = f"{sheet_name}.csv"
        
        # Guardar el DataFrame en un archivo CSV
        df.to_csv(nombre_archivo_csv, index=False)
        
        print(f"Hoja '{sheet_name}' exportada a '{nombre_archivo_csv}'.")

    print("Todas las hojas han sido exportadas a CSV.")

nombre_archivo_excel = 'Datos proyecto E2.xlsx'

# Llamar a la función para convertir todas las hojas en archivos CSV
excel_a_csv(nombre_archivo_excel)
