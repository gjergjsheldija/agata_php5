<?php

class AgataCompatibility {

    function readReport($FileName) {
        $array = Xml2Array($FileName);
        # From 7.2 to 7.5
        if (!$array['Version']) {
            $new_array['Version'] = '7.4';
            $new_array['Properties']['Description'] = $array['description'];
            $new_array['Header']['Body'] = $array['header']['body'];
            $new_array['Header']['Align'] = $array['header']['align'];
            $new_array['Footer']['Body'] = $array['footer']['body'];
            $new_array['Footer']['Align'] = $array['footer']['align'];
            $new_array['PageSetup']['Format'] = $array['preferences']['pagesetup']['format'];
            $new_array['PageSetup']['Orientation'] = $array['preferences']['pagesetup']['orientation'];
            $new_array['PageSetup']['LeftMargin'] = $array['preferences']['pagesetup']['marginleft'];
            $new_array['PageSetup']['RightMargin'] = $array['preferences']['pagesetup']['marginright'];
            $new_array['PageSetup']['TopMargin'] = $array['preferences']['pagesetup']['margintop'];
            $new_array['PageSetup']['BottomMargin'] = $array['preferences']['pagesetup']['marginbottom'];
            $new_array['PageSetup']['LineSpace'] = $array['preferences']['pagesetup']['linespace'];
            $new_array['Parameters'] = $array['parameters'];
            $new_array['DataSet']['DataSource']['Name'] = $array['datasource']['name'];
            $new_array['DataSet']['Query']['Select'] = $array['query']['select'];
            $new_array['DataSet']['Query']['From'] = $array['query']['from'];
            $new_array['DataSet']['Query']['Where'] = $array['query']['where'];
            $new_array['DataSet']['Query']['GroupBy'] = $array['query']['groupby'];
            $new_array['DataSet']['Query']['OrderBy'] = $array['query']['orderby'];
            $new_array['DataSet']['Query']['Config']['Distinct'] = $array['preferences']['distinct'];
            $new_array['DataSet']['Groups']['Config']['ShowGroup'] = $array['preferences']['showgroup'];
            $new_array['DataSet']['Groups']['Config']['ShowDetail'] = $array['preferences']['showdetail'];
            $new_array['DataSet']['Groups']['Config']['ShowLabel'] = $array['preferences']['showlabel'];
            $new_array['DataSet']['Groups']['Config']['ShowNumber'] = $array['preferences']['shownumber'];
            $new_array['DataSet']['Groups']['Config']['ShowIndent'] = $array['preferences']['showindent'];

            if ($array['groups']) {
                foreach ($array['groups'] as $group => $formulas) {
                    $new_array['DataSet']['Groups']['Formulas'][ucwords($group)] = $formulas;
                }
            }

            if ($array['adjustments']) {
                foreach ($array['adjustments'] as $column => $properties) {
                    foreach ($properties as $property => $value) {
                        $new_array['DataSet']['Fields'][ucwords($column)][ucwords($property)] = $value;
                    }
                }
            }

            $new_array['Graph']['Title'] = $array['graph']['title'];
            $new_array['Graph']['TitleX'] = $array['graph']['titlex'];
            $new_array['Graph']['TitleY'] = $array['graph']['titley'];
            $new_array['Graph']['Width'] = $array['graph']['width'];
            $new_array['Graph']['Height'] = $array['graph']['height'];
            $new_array['Graph']['Description'] = $array['graph']['description'];
            $new_array['Graph']['ShowData'] = $array['graph']['showdata'];
            $new_array['Graph']['ShowValues'] = $array['graph']['showvalues'];
            $new_array['Graph']['Orientation'] = $array['graph']['orientation'];

            $new_array['Merge']['ReportHeader'] = $array['merge']['header'];
            $new_array['Merge']['Details']['Detail1']['GroupHeader'] = $array['merge']['groupheader'];
            $new_array['Merge']['Details']['Detail1']['DataSet1']['Body'] = $array['merge']['detail'];
            $new_array['Merge']['Details']['Detail1']['DataSet1']['Query']['Select'] = $array['merge']['query']['select'];
            $new_array['Merge']['Details']['Detail1']['DataSet1']['Query']['From'] = $array['merge']['query']['from'];
            $new_array['Merge']['Details']['Detail1']['DataSet1']['Query']['Where'] = $array['merge']['query']['where'];
            $new_array['Merge']['Details']['Detail1']['DataSet1']['Query']['GroupBy'] = $array['merge']['query']['groupby'];
            $new_array['Merge']['Details']['Detail1']['DataSet1']['Query']['OrderBy'] = $array['merge']['query']['orderby'];
            $new_array['Merge']['Details']['Detail1']['GroupFooter'] = $array['merge']['groupfooter'];
            $new_array['Merge']['ReportFooter'] = $array['merge']['footer'];

            $new_array['Merge']['PageSetup']['Format'] = $array['merge']['pagesetup']['format'];
            $new_array['Merge']['PageSetup']['Orientation'] = $array['merge']['pagesetup']['orientation'];
            $new_array['Merge']['PageSetup']['LeftMargin'] = $array['merge']['pagesetup']['marginleft'];
            $new_array['Merge']['PageSetup']['RightMargin'] = $array['merge']['pagesetup']['marginright'];
            $new_array['Merge']['PageSetup']['TopMargin'] = $array['merge']['pagesetup']['margintop'];
            $new_array['Merge']['PageSetup']['BottomMargin'] = $array['merge']['pagesetup']['marginbottom'];
            $new_array['Merge']['PageSetup']['LineSpace'] = $array['merge']['pagesetup']['linespace'];

            if ($array['merge']['adjustments']) {
                foreach ($array['merge']['adjustments'] as $column => $properties) {
                    foreach ($properties as $property => $value) {
                        $new_array['Merge']['Details']['Detail1']['DataSet1']['Fields'][ucwords($column)][ucwords($property)] = $value;
                    }
                }
            }

            $new_array['Label']['Body'] = $array['label']['body'];
            $new_array['Label']['Config']['HorizontalSpacing'] = $array['label']['config']['horizontal_spacing'];
            $new_array['Label']['Config']['VerticalSpacing'] = $array['label']['config']['vertical_spacing'];
            $new_array['Label']['Config']['LabelWidth'] = $array['label']['config']['label_width'];
            $new_array['Label']['Config']['LabelHeight'] = $array['label']['config']['label_height'];
            $new_array['Label']['Config']['LeftMargin'] = $array['label']['config']['left_margin'];
            $new_array['Label']['Config']['TopMargin'] = $array['label']['config']['top_margin'];
            $new_array['Label']['Config']['Columns'] = $array['label']['config']['label_cols'];
            $new_array['Label']['Config']['Rows'] = $array['label']['config']['label_rows'];
            $new_array['Label']['Config']['PageFormat'] = $array['label']['config']['page_format'];
            $new_array['Label']['Config']['LineSpacing'] = $array['label']['config']['line_spacing'];

            return $new_array;
        } elseif ($array['Version'] == '7.4') { # From 7.4 to 7.5
            $array['Version'] = '7.5';
            $array['Report']['Merge']['Details']['Detail1']['NumberSubSql'] = 0;
            $array['Merge']['Details']['Detail1']['DataSet1'] = $array['Merge']['Details']['Detail1']['DataSet'];
            $array['Merge']['Details']['Detail1']['DataSet1']['Body'] = $array['Merge']['Details']['Detail1']['Body'];

            unset($array['Merge']['Details']['Detail1']['Body'],
                    $array['Merge']['Details']['Detail1']['DataSet']);
        }

        return $array;
    }

}

?>