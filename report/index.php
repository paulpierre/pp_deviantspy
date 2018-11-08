<?php
header("Access-Control-Allow-Origin: *");

/**
 * User: paulpierre
 * Date: 3/8/16
 * Time: 1:20 PM
 * To change this template use File | Settings | File Templates.
 */
$ip = Array(
    '##########',
    '##########',
    '##########',
    '##########',
    '##########',
    '##########',
    '##########',
    '##########',
    '##########',
    '##########',
    '##########',

);


if (isset($_GET['report'])) $report = $_GET['report'];
    else $report = 'creative';

$widget_url = '';
if($report == 'widget')
{
   if(!isset($_GET['widget_url'])) exit('No widget url provided');
        else $widget_url = $_GET['widget_url'];
}

$geo = (isset($_GET['geo']))?$_GET['geo']:'all';
$network = (isset($_GET['network']))?$_GET['network']:'1';

if (isset($_GET['reset_cache'])) $reset_cache = $_GET['reset_cache'];
else $reset_cache = 0;




//if(!in_array($_SERVER['REMOTE_ADDR'],$ip))
//    exit('<html><head><title>Unauthorized Access</title></head><body><center><h1>Your IP ' .$_SERVER['REMOTE_ADDR'].' is not authorized to access this URL.</h1><br/><img src="http://vignette3.wikia.nocookie.net/villainsfanon/images/a/a7/Skull_and_Crossbones.jpg"/></center></body></html>');
?>

<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>DeviantSpy - (ALPHA)</title>


        <style type="text/css">
            html, body{
                min-height: 100%;
            }
            body{
                position: relative;
            }

            tbody {
                font-size:10px;
                font-family:Arial;
            }

            thead tr {
                font-weight:bold;
                font-size:13px;
                font-family:Arial;
                overflow:hidden;
                text-overflow: ellipsis;
            }

            thead tr td {
                text-align:center;
                vertical-align: middle;
            }

            tbody td {
                font-size:10px;
                overflow:hidden;
                text-overflow: ellipsis;
                vertical-align: middle;
            }

            #report_controls {
                width:80%;
                margin-left:0px;
            }
            #report_controls div {
                float:left;
                margin-right:25px;
                padding-bottom:10px;
            }

            .ds_thumb {
               width:88px;
               height:66px;
            }

            .ds_headline {
                font-size:12px;
            }

            .ds_widget_count,.ds_widget_id {
                font-size:15px;
            }
            .overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10;
                background-color: rgba(0,0,0,0.5); /*dim the background*/
            }

            .loading_modal {
                width: 500px;
                height: 400px;
                position: fixed;
                top: 50%;
                left: 50%;
                margin-top: -200px;
                margin-left: -250px;
                background-color: #FFFEFE;
                text-align: center;
                z-index: 11; /* 1px higher than the overlay layer */
                border-radius:7px;
                border:3px solid #EEE;
            }


            .loading_modal img {
                margin-top:20px;
            }

            .loading_modal h4 {
                font-size:30px;
            }

            .title_header {
                width:100%;border-bottom:solid 1px #c0c0c0;float:left;margin-bottom:50px;padding-bottom:20px;
            }

            .center_middle {
                text-align: center !important;
                vertical-align: middle !important;
            }

            #report_header {
                margin-left:60px;
                margin-bottom:10px;
            }

            #report {
                table-layout:fixed;
                width:100%;
            }

            #report_container {
                width:95%;
                margin:0 auto !important;
                float:none;
            }

            .ds_ago {
                font-size:12px;
            }

        </style>
        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-##########-1', 'auto');
            ga('send', 'pageview');

        </script>
        <script   src="https://code.jquery.com/jquery-2.2.3.min.js"   integrity="sha256-a23g1Nt4dtEYOj7bR+vTu7+T8VP13humZFBJNIYoEJo="   crossorigin="anonymous"></script>        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

        <script src="http://js.anonym.to/anonym/anonymize.js" type="text/javascript"></script>

        <link rel="stylesheet" type="text/css" href="http://cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css">
        <link rel="stylesheet" type="text/css" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.11/css/dataTables.bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.1.1/css/fixedHeader.dataTables.min.css">
        <link rel="stylesheet" type="text/css" href="css/animate.css">

        <link rel="stylesheet" type="text/css" href="css/tether.min.css">
        <link rel="stylesheet" type="text/css" href="css/tether-theme-basic.css">
        <script src="https://cdn.jsdelivr.net/clipboard.js/1.5.8/clipboard.min.js"></script>



        <script type="text/javascript" charset="utf8" src="js/tether.min.js"></script>
        <script type="text/javascript" charset="utf8" src="http://cdn.datatables.net/1.10.11/js/jquery.dataTables.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/js/bootstrap.min.js" integrity="sha384-vZ2WRJMwsjRMW/8U7i6PWi6AlO1L79snBrmgiDpgIWJ82z8eA5lenwvxbMV1PAh7" crossorigin="ar.min.js"></script>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/fixedheader/3.1.1/js/dataTables.fixedHeader.min.js"></script>
        <script type="text/javascript" charset="utf8" src="js/ds.js"></script>

        <script type="text/javascript" class="init">
            var country = '<? print $geo; ?>',report_order,widget_list='',img_loading,network= <? print $network; ?>,display,report='<? print $report; ?>',report_columns,report_column_defs,table,widget_url='<? print $widget_url; ?>',reset_cache=<? print $reset_cache; ?>;

            $(document).ready(function() {
                $('.overlay').fadeOut('fast');
                $('.loading_modal h4').text('Loading "'+ report +'" report.. ');
                protected_links = "";

                auto_anonymize();


                img_loading = [
                    'http://i.imgur.com/WtqJERO.gif',
                    '/img/loading01.gif-c200',
                    'img/loading02.gif',
                    'img/loading03.gif',
                    'img/loading04.gif',
                    'img/loading05.gif',
                    'img/loading06.gif',
                    'img/loading07.gif',
                    'img/loading08.gif',
                    'img/loading09.gif',
                    'img/loading10.gif',
                    'img/loading11.gif'
                ];


                /*

                    $('body').on('click','button#copy_widget',function(e){
                    e.preventDefault();


                $('#widget_report').DataTable({
                    "order": [[ 5, "asc" ]],
                    "pageLength": 500,
                    "lengthMenu": [500, 1000, 1500,2000 ],
                    "sDom":'fptip',
                    fixedHeader: true,
                    "info":     true
                });
                */


                switch(report)
                {
                    case 'creative2':
                        $('#report_header,#select_report button strong').text('View Creatives 2 (<span style="color:#0072b5"></span>)');

                        report_order = [[ 0, "desc"]];
                        report_columns = [
                            { "data": "widget_count" },
                            { "data": "img_url" },
                            { "data": "headline" },
                            { "data": "widget_avg_position" },
                            { "data": "view_count" },
                            { "data": "affiliate_count" },
                            { "data": "publisher_count" },
                            { "data": "first_seen" },
                            { "data": "last_seen" }
                        ];

                        report_column_defs = [
                            {
                                "render": function ( data, type, row ) {
                                    return '<a href="#" class="ds_widget_count"/>'+data+'</a>';
                                },
                                "className":"center_middle",
                                "width":"20px",
                                "targets": [0]
                            },
                            {
                                "render": function ( data, type, row ) {
                                    return '<img class="ds_thumb" src="'+data+'"/>';
                                },
                                "className":"center_middle",
                                "width":"65px",
                                "targets": [1]
                            },
                            {
                                "render": function ( data, type, row ) {
                                    return '<strong class="ds_headline">'+data+'</strong>';
                                },
                                "className":"center_middle",
                                "width":"300px",
                                "targets": [2]
                            },



                            {   "render": function ( data, type, row ) {
                                return '<span style="font-size:14px;">' + data + '</span>';
                            },
                                "className":"center_middle",
                                "width":"20px",
                                "targets": [3,4,5,6]
                            },
                            {
                                "className":"center_middle",
                                "width":"70px",
                                "targets": [7,8]
                            }


                        ];
                        break;
                    break;


                    case 'creative':
                        $('#report_header,#select_report button strong').html('View Creatives (<span style="color:#0072b5"></span>)');

                        report_order = [[ 0, "desc"]];
                        report_columns = [
                            { "data": "widget_count" },
                            { "data": "img_url" },
                            { "data": "headline" },
                            { "data": "publisher_url" },
                            { "data": "last_position" },
                            { "data": "widget_avg_position" },
                            { "data": "view_count" },
                            { "data": "first_seen" },
                            { "data": "last_seen" },
                            { "data": "destination_url" },
                            { "data": "offer_domain" }
                        ];

                        report_column_defs = [
                            {
                                "render": function ( data, type, row ) {
                                    return '<a href="#" class="ds_widget_count"/>'+data+'</a>';
                                },
                                "className":"center_middle",
                                "width":"20px",
                                "targets": [0]
                            },
                            {
                                "render": function ( data, type, row ) {
                                    return '<img class="ds_thumb" src="'+data+'"/>';
                                },
                                "className":"center_middle",
                                "width":"65px",
                                "targets": [1]
                            },
                            {
                                "render": function ( data, type, row ) {
                                    return '<strong class="ds_headline">'+data+'</strong>';
                                },
                                "className":"center_middle",
                                "width":"300px",
                                "targets": [2]
                            },
                            {
                                "render": function ( data, type, row ) {
                                    return '<a class="ds_publisher" href="' + data + '" target="_blank">'+data+'</a>';
                                },
                                "className":"center_middle",
                                "width":"70px",
                                "targets": [3]
                            },


                            {   "render": function ( data, type, row ) {
                                return '<span style="font-size:14px;">' + data + '</span>';
                            },
                                "className":"center_middle",
                                "width":"20px",
                                "targets": [4,5,6]
                            },
                            {
                                "className":"center_middle",
                                "width":"70px",
                                "targets": [7,8,9]
                            },

                            {
                                "render": function ( data, type, row ) {
                                    return '<a class="ds_click_url" href="' + data + '" target="_blank">'+data+'</a>';
                                },
                                "className":"center_middle",
                                "width":"150px",
                                "targets": [10]
                            },

                            {
                                "className":"center_middle",
                                "width":"75px",
                                "targets": [11]
                            }

                        ];
                    break;

                    case 'widget':
                        $('#report_header').html('Widget List (<span style="color:#0072b5"></span>)');
                        report_order = [[ 6, "desc"]];

                        report_columns = [
                            { "data": "widget_id" },
                            { "data": "img_url" },
                            { "data": "headline" },
                            { "data": "publisher_url" },
                            { "data": "last_position" },
                            { "data": "widget_avg_position" },
                            { "data": "view_count" },
                            { "data": "first_seen" },
                            { "data": "last_seen" },
                            { "data": "duration"},
                            { "data": "destination_url" }
                        ];

                        report_column_defs = [
                            {
                                "render": function ( data, type, row ) {
                                    return '<strong class="widget_id">'+data+'</strong>';
                                },
                                "width":"20px",
                                "targets": [0]
                            },
                            {
                                "render": function ( data, type, row ) {
                                    return '<img class="ds_thumb" src="'+data+'"/>';
                                },
                                "width":"65px",
                                "targets": [1]
                            },
                            {
                                "render": function ( data, type, row ) {
                                    return '<strong class="ds_headline">'+data+'</strong>';
                                },
                                "width":"300px",
                                "targets": [2]
                            },
                            {
                                "render": function ( data, type, row ) {
                                    return '<a class="ds_publisher" href="' + data + '" target="_blank">'+data+'</a>';
                                },
                                "width":"200px",
                                "targets": [3]
                            },


                            {   "render": function ( data, type, row ) {
                                return '<span style="font-size:14px;">' + data + '</span>';
                            },
                                "width":"10px",
                                "targets": [4,5,6]
                            },

                            {
                                "width":"100px",
                                "targets": [10]
                            }

                        ];


                    break;
                }

                table = $('#report')
                        .on('preXhr.dt', function ( e, settings, data ) {
                            $('#img_loading').attr('src',img_loading[Math.floor((Math.random() * img_loading.length))]);

                            widget_list = '';
                                    $('.overlay').fadeIn('fast');
                        } )
                        .on('xhr.dt', function ( e, settings, json, xhr ) {
                            //console.log(json);
                            //load_widgets(json);
                            var txt = $('#report_header').text();
                            $('#report_header span').text(numberWithCommas(json.recordsTotal));

                            $('.overlay').fadeOut('fast');

                        } )
                        .DataTable({
                            order: report_order,
                            pageLength: 500,
                            "sPaginationType": "full_numbers",
                            "sDom": '<"top"flp>rt<"bottom"i><"clear">',
                            lengthMenu: [500, 1000, 1500,2000 ],
                            fixedHeader: true,
                            info: true,
                            serverSide: true,
                            autoWidth: false,
                            //columns:report_columns,
                            ajax: {
                                url: 'http://api.deviantspy.com/report/',
                                type: 'POST',
                                pages:5,
                                data: function(d){
                                    var i ={};
                                    $.extend(i, d, {
                                        "geo":country,
                                        "network":network,
                                        "report":report,
                                        "widget_url":widget_url,
                                        "reset_cache":reset_cache
                                    });
                                    return i;
                                }
                            },
                            "columnDefs": report_column_defs,

                            "createdRow": function( row, data, dataIndex ) {
                                if(report=="widget")
                                {
                                    widget_list +=data[0] + ',';
                                }
                            }

                });

                $('#report').on('draw.dt',function(){
                    if(report=="widget")
                    {
                        widget_list = widget_list.replace(/,\s*$/, "");
                        $('#widget_list').val(widget_list);
                    }

                });


                $('#select_country ul li a').on('click',function(o){
                    country = $(this).attr('data');
                    display = $(this).text();
                    $('#select_country button strong').text(display);
                    console.log('Country selected: ' + display);
                    table.ajax.reload(function(json){
                        console.log(json);
                    },true);
                });


                $('#select_network ul li a').on('click',function(o){
                    network = parseInt($(this).attr('data'));
                    display = $(this).text();
                    $('#select_network button strong').text(display);
                    console.log('Network selected: ' + network);
                    table.ajax.reload(function(json){
                        console.log(json);
                    },true);
                });

                $('#select_report ul li a').on('click',function(o){
                    report = $(this).attr('data');
                    display = $(this).text();
                    $('#select_report button strong').text(display);
                    console.log('Report selected: ' + report);
                    table.ajax.reload(function(json){
                        console.log(json);
                    },true);
                });


                $('#report').on('click','a.ds_widget_count',function(e){
                    e.preventDefault();
                    var _wurl = $(this).parent().parent().find('a.ds_click_url').attr('href');
                    var url='/?report=widget&widget_url='+encodeURIComponent(_wurl);
                    console.log(url);
                    window.open(url,'_blank');
                });

                var clipboard = new Clipboard('#copy_widget');


                $('body').on('click','#copy_widget',function(e){
                    $('#widget_list').animateCss('bounce');
                    console.log('copied!');
                });





                $('body').on('click','#reset_cache',function(e){
                    e.preventDefault();
                    window.open('/?report='+report+'&reset_cache=1','_blank');
                });

/*
                function load_widgets(json)
                {
                    //console.log(json);
                    if(report != 'widget') return;

                    $(json.data).each(function(key,val){
                        if(val[0] !== "undefined")
                            widget_list +=val[0] + ',';

                    });
                    widget_list = widget_list.replace(/,\s*$/, "");
                    $('#widget_list').val(widget_list);
                }
*/
                $.fn.extend({
                    animateCss: function (animationName) {
                        var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
                        $(this).addClass('animated ' + animationName).one(animationEnd, function() {
                            $(this).removeClass('animated ' + animationName);
                        });
                    }
                });

                function numberWithCommas(x) {
                    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }




            } );
        </script>
    </head>
    <body>
    <div class="overlay"><div class="loading_modal"><h4></h4><img id="img_loading" src="" width="400" height="300"/></div></div>

    <div class="title_header">
            <img src="http://clashofkings.alphagamestrategies.com/wp-content/uploads/2015/09/british_spy.jpg" width="72" height="50" style="float:left;"/>
            <h1>DeviantSpy</h1>
    </div>

    <h3 id="report_header"></h3>
    <div id="report_container">
        <div id="report_controls">

            <div class="dropdown" id="select_report">
                <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Report: <strong>View Creatives</strong>
                    <span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <li><a href="#" data="creative">View Creatives</a></li>
                    <li><a href="#" data="creative2">View Creatives 2</a></li>

                </ul>
            </div>
            <div class="dropdown" id="select_country">
                <button class="btn  dropdown-toggle" type="button" data-toggle="dropdown">Country: <strong>All</strong>
                    <span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <li><a href="#" data="all">All</a></li>
                    <li><a href="#" data="us">US</a></li>
                    <li><a href="#" data="ca">CA</a></li>
                    <li><a href="#" data="au">AU</a></li>
                    <li><a href="#" data="nz">NZ</a></li>
                    <li><a href="#" data="my">MY</a></li>
                    <li><a href="#" data="sg">SG</a></li>
                    <li><a href="#" data="hk">HK</a></li>
                </ul>
            </div>

            <div class="dropdown" id="select_network">
                <button class="btn  dropdown-toggle" type="button" data-toggle="dropdown">Network: <strong>All</strong>
                    <span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <li><a href="#" data="0">All</a></li>
                    <li><a href="#" data="1">Revcontent</a></li>
                    <li><a href="#" data="2">Content.Ad</a></li>
                </ul>
            </div>

            <button class="btn btn-danger" id="reset_cache" type="button" data-toggle=""><strong>Clear cache</strong></button>

            <?
            if($report=='widget')
            { ?>
                <input id="widget_list" value="List of widgets"/>
                <button class="btn btn-warning" id="copy_widget" type="button" data-clipboard-action="copy" data-clipboard-target="#widget_list">Copy Widgets</button>
                <?
            }

            ?>

        </div>
    <?

    switch($report)
    {
        case 'creative2':
            /**
             *                 $item['widget_count'],
            $item['img_url'],
            $item['headline'],
            $item['widget_avg_position'],
            $item['view_count'],
            $item['affiliate_count'],
            $item['publisher_count'],
            $item['first_seen'] .'<br><strong class="ds_ago">'. time_ago(strtotime($item['first_seen'])).' ago</strong>',
            $item['last_seen'] .'<br><strong class="ds_ago">'. time_ago(strtotime($item['first_seen'])) . ' ago</strong>'
             */
            ?>
            <table id="report" class="table table-striped table-bordered display" cellspacing="0">
                <thead>
                <tr>
                    <th># Widget</th>
                    <th>Image</th>
                    <th>Headline</th>
                    <th>Avg. Pos</th>
                    <th>Views</th>
                    <th>Aff. count</th>
                    <th>Pub. count</th>
                    <th>First Seen</th>
                    <th>Last Seen</th>
                </tr>
                </thead>

                <tbody>
                    <td># Widget</td>
                    <td>Image</td>
                    <td>Headline</td>
                    <td>Avg. Pos</td>
                    <td>Views</td>
                    <td>Aff. count</td>
                    <td>Pub. count</td>
                    <td>First Seen</td>
                    <td>Last Seen</td>
                </tbody>
            </table>

            <?
            break;


        case 'creative':
            ?>
            <table id="report" class="table table-striped table-bordered display" cellspacing="0">
                <thead>
                <tr>
                    <th># Widget</th>
                    <th>Image</th>
                    <th>Headline</th>
                    <th>Pub</th>

                    <th>Last Pos.</th>
                    <th>Avg. Pos</th>
                    <th>Views</th>
                    <th>First Seen</th>
                    <th>Last Seen</th>
                    <th>Duration</th>
                    <th>Click URL</th>
                    <th>Affiliate</th>


                </tr>
                </thead>

                <tbody>
                    <td># Widget</td>
                    <td>Image</td>
                    <td>Headline</td>
                    <td>Pub</td>

                    <td>Last Pos.</td>
                    <td>Avg. Pos</td>
                    <td>Views</td>
                    <td>First Seen</td>
                    <td>Last Seen</td>
                    <td>Duration</td>
                    <td>Click URL</td>
                    <td>Affiliate</td>
                </tbody>
            </table>

            <?
            break;

        case 'widget':
        ?>

            <table id="report" class="table table-striped table-bordered display" cellspacing="0" width="90%">
                <thead>
                <tr>
                    <th>Widget ID</th>
                    <th>Image</th>
                    <th>Headline</th>
                    <th>Pub</th>
                    <th>Last Pos.</th>
                    <th>Avg. Pos.</th>
                    <th>Views</th>
                    <th>First seen</th>
                    <th>Last seen</th>
                    <th>Duration</th>
                    <th>Affiliate</th>
                </tr>
                </thead>

                <tbody>
                <tr>
                    <td>Widget ID</td>
                    <td>Image</td>
                    <td>Headline</td>
                    <td>Pub</td>
                    <td>Last Pos.</td>
                    <td>Avg. Pos.</td>
                    <td>Views</td>
                    <td>First seen</td>
                    <td>Last seen</td>
                    <td>Duration</td>
                    <td>Affiliate</td>
                </tr>
                </tbody></table>


        <?
        break;
    }
?>

    </div>

    </body>
</html>
