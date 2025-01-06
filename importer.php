<?php

namespace ExcelImport;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;


class importer {

    public static function admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( isset( $_POST['submit'] ) ) {
            self::process_import();
        }

        ?>
        <div class="wrap">
            <h3>Importa prodotti</h3>
            <form method="post" enctype="multipart/form-data" id="excel-import">
                <label for="file">Selezioni il file da importare: <br>
                    <input type="file" name="file" required /></label>

                <label for="confirm-overwrite">
                    <input type="checkbox" name="confirm-overwrite" required />
                    Capisco che caricando il file qui andrà a sovrascrivere i dati esistenti.
                </label>

                <p><button type="submit" name="submit">Submit</button></p>
            </form>
        </div>
        <?php
        self::display_data();
    }


    public static function process_import() {
        if ( ! current_user_can( 'manage_options' ) ) {
            echo "You do not have permission to do this";
            return;
        }

        if ( empty( $_FILES['file'] ) ) {
            echo "No file uploaded";
            return;
        }

  
        if (isset($_POST['submit'])) {
        
            $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            
            if(isset($_FILES['file']['name']) && in_array($_FILES['file']['type'], $file_mimes)) {
                
                $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            
                if('csv' == $extension) {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                } else {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                }
        
                $reader->setLoadAllSheets();
                $spreadsheet = $reader->load($_FILES['file']['tmp_name']);
                        
                $sheetNames = $spreadsheet->getSheetNames();
                $sheetData = array();

                foreach ($sheetNames as $sheetName) {
                    $spreadsheet->setActiveSheetIndexByName($sheetName);
                    
                    // If we find a sheet called "TU" we label it as "Decoro";
                    // If we find a sheet called "DIGITALE" we label it as "Laminato";
                    // Any other label will remain unchanged
                    $sheet_label = ($sheetName == "TU") ? "Decoro" : (($sheetName == "DIGITALE") ? "Laminato" : $sheetName);

                    $sheetData[$sheet_label] = $spreadsheet->getActiveSheet()->toArray();                
                }

                database::clear_table();
                foreach($sheetData as $sheetLabel => $sheetData) {
      
                    if (!empty($sheetData)) {
                        for ($i=1; $i<count($sheetData); $i++) { //skipping first row
                            $key = strip_tags($sheetData[$i][0]);
                            $val = strip_tags($sheetData[$i][1]);
                            $flag = strip_tags($sheetLabel);

                            // Jump if row has sheet label in it
                            if ( strtoupper($key) === "LAMINATO" || strtoupper($key) === "DECORO") {
                                continue;
                            }
            
                            $data = array(
                                'time' => current_time('mysql'),
                                'elemento' => $key,
                                'corrispondente' => $val,
                                'tipologia' => $flag
                            );

                            if(empty($data['corrispondente']) || empty($data['elemento']) || empty($data['tipologia']))
                            {
                                echo "Empty or misconfigured data found at row $i. Skipping.";
                                echo "Found: " . print_r($data, true);
                                echo "</br>";
                                continue;
                            }

                            database::insert_data($data);
                        }
                    }
                }

                

                echo "Records inserted successfully.";
            } else {
                echo "Upload only CSV or Excel file.";
            }

        }
    }


    public static function display_data() {
        $data = database::get_data();
        if ( empty( $data ) ) {
            return;
        }
        ?>
        <h3>Dati attuali</h3>
        <p class="import-note">L'ultimo import risale al 
            <?php 
                $last_import = $data[0]->time;
                echo date('d/m/Y H:i', strtotime($last_import));
            ?>
        </p>
        <input type="text" id="table-filter" placeholder="Filter table">
        <script>
            jQuery(document).ready(function($) {
                $('#table-filter').on('keyup', function() {
                    var value = $(this).val().toLowerCase();
                    $('table.wp-list-table tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                });
            });
        </script>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Elemento</th>
                    <th>Corrispondente</th>
                    <th>Tipologia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $data as $row ) : ?>
                    <tr>
                        <td><?php echo $row->elemento; ?></td>
                        <td><?php echo $row->corrispondente; ?></td>
                        <td><?php echo $row->tipologia; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

}   