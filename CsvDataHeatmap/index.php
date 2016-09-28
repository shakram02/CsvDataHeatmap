
<html>
<head>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
</head>
<body>

    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="#">Heatmap generator</a>
            </div>
            <ul class="nav navbar-nav"></ul>
        </div>
    </nav>
    <div class="container-fluid">
        <form class="form-inline" action="upload.php" method="post" enctype="multipart/form-data">

            <div class="form-group">
                <label>Select a CSV file to upload:</label>
                <label class="btn btn-default btn-file">
                    <input type="file" name="fileToUpload" id="fileToUpload" />
                </label>
                <!--<input type="file" class="form-control" name="fileToUpload" id="fileToUpload" />-->
                <input type="submit" class="btn btn-info" value="Upload File" name="submit" />
            </div>
        </form>
    </div>
	<!--Update-->

</body>
</html>

<?php



static $colors=array(
array(0x00,0x00,0xFF),  // Blue
array(0x00,0x80,0x00),  // Green
array(0xFF,0xFF,0x00),  // Yellow
array(0xFF,0xA5,0x00),  // Yellow
array(0xFF,0x00,0x00),  // Red
);

$colorsLength=count($colors);

function parse_csv_file($fileName)
{
    $csv = array_map('str_getcsv', file($fileName));
    $headers= $csv[0];

    array_shift($csv);  // remove column header

    global $rows;
    global $minVals;
    global $maxVals;
    global $norm;

    $numberOfLines=count($csv);
    $numberOfCols = count($csv[0]);

    for ($i = 0; $i < $numberOfCols; $i++)
    {
        for ($j = 0; $j < $numberOfLines; $j++)
        {
            // Rotate the matrix to $rows
            $rows[$i][$j]=$csv[$j][$i];
        }
    }

    for ($j = 0; $j < $numberOfCols; $j++)
    {
        //if(!is_numeric($rows[$j][0]))
        //{
        //    // Drop this row from the array
        //    unset($rows[$j]);

        //    continue;
        //}
        //Get the min and max value for each data column
        $minVals[$j] = min($rows[$j]);
        $maxVals[$j] = max($rows[$j]);
    }

    // Copy the values with valid keys to the headers array
    //for ($i = 0; $i < $numberOfCols; $i++)
    //{
    //    if(key_exists($i,$rows)== false)
    //    {
    //        unset($headers[$i]);
    //    }
    //}


    // Clean up the mess by removing invalid values
    $rows = array_values($rows);
    $minVals=array_values($minVals);
    $maxVals=array_values($maxVals);
    $headers = array_values($headers);

    $numberOfCols = count($rows);
    $numberOfLines = count($rows[0]);


    //Normalize table
    $norm = normalize_table($numberOfCols,$numberOfLines,$minVals,$maxVals,$rows);
?>


<body>
    <div class="container-fluid">
        <div style="overflow:scroll;">
            <table class="table table-inverse table-bordered" style="white-space: nowrap">

                <?php

    foreach (array_keys($rows) as $colKey)
    {
                ?>
                <tr>

                    <th align="center">
                        <b>
                            <font face="Arial, Helvetica, sans-serif">
                                <?php echo $headers[$colKey]?>
                            </font>
                        </b>
                    </th>

                    <?php
        // $j= 1 to ignore column header
        foreach (array_keys($rows[$colKey]) as $cellKey)
        {
            $val = $rows[$colKey][$cellKey];
            if(key_exists($colKey,$norm) && key_exists($cellKey,$norm[$colKey]))
            {
                $normVal=$norm[$colKey][$cellKey];
                echo create_table_row($colKey,$cellKey,$normVal,$val);
            }
            else
            {
                    ?>

                    <td align="center">
                        <font class="text-muted" face="Arial, Helvetica, sans-serif">
                            <?php echo $val?>
                        </font>
                    </td>
                    <?php
            }
        }
                    ?>
                </tr>
                <?php
    }
}
                ?>
            </table>
        </div>
    </div>
</body>

<?php

function normalize_table($numberOfCols,$numberOfLines,array $minVals,array $maxVals,array $rows)
{
    for ($i = 0; $i < $numberOfCols; $i++)
    {
        $minVal= $minVals[$i];
        $maxVal=$maxVals[$i];
        for ($j = 0; $j < $numberOfLines; $j++)
        {
            if($maxVal == 0) break;
            $result[$i][$j] = ($rows[$i][$j] - $minVal) / ($maxVal - $minVal);
        }
    }
    return $result;
}

function create_table_row($colNum,$j,$normVal,$originalVal)
{
    global $minVals;
    global $maxVals;

    $color=interpolate($normVal,$minVals[$colNum],$maxVals[$colNum]);
    return ' <td  style="background-color:'.$color.';" align="center"><font class="text-muted" face="Arial, Helvetica, sans-serif">'.$originalVal.'</font></td>';

}

function interpolate($value, $minVal, $maxVal)
{
    global $colors;
    global $colorsLength;

    if ($maxVal == 0.00) return "0x000000";

    //$percent = ($value - $minVal) / ($maxVal - $minVal);

    $left = floor($value * ($colorsLength - 1));
    $right = ceil($value* ($colorsLength- 1));
    $colorLeft = $colors[$left];
    $colorRight = $colors[$right];

    $step = 1.0 / ($colorsLength - 1);
    $percentRight = ($value - ($left * $step)) / $step;
    $percentLeft = 1.0 - $percentRight;

    $r = $colorLeft[0] * $percentLeft + $colorRight[0] * $percentRight;
    $g = $colorLeft[1] * $percentLeft + $colorRight[1] * $percentRight;
    $b = $colorLeft[2] * $percentLeft + $colorRight[2] * $percentRight;

    return sprintf("#%02x%02x%02x", $r, $g, $b);
    //return rgb2hex2rgb('\''.$R.','.$G.','.$B.'\'');
    //return dechex($R).dechex($G).dechex($B);
}

?>