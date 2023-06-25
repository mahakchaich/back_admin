<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SqlImportController extends Controller
{
    public function importSql(Request $request)
    {
        $sqlFilePath = $request->file('sql_file')->getRealPath();
        $sql = file_get_contents($sqlFilePath);
        $sqlStatements = explode(';', $sql);

        foreach ($sqlStatements as $statement) {
            $statement = trim($statement);

            if (!empty($statement)) {
                DB::statement($statement);
            }
        }

        return response()->json(['message' => 'SQL file imported successfully!']);
    }

    public function importCsv(Request $request)
    {
        if ($request->hasFile('csv_file')) {
            $path = $request->file('csv_file')->getRealPath();

            // Open the file with read mode and custom delimiter
            $file = fopen($path, 'r');
            $delimiter = "\t";

            // Read the header row
            $header = fgetcsv($file, 0, $delimiter);

            while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
                $rowData = array_combine($header, $row);

                // Skip the 'id' column during insertion
                if (isset($rowData['id'])) {
                    unset($rowData['id']);
                }

                // Assuming 'YourTableName' is the name of your table
                DB::table('partners')->insert($rowData);
            }

            // Close the file
            fclose($file);
        };
    }
}
