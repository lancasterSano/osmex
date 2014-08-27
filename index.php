<?php
include './server_scripts/config.php';
global $array;
$sql = "SELECT Cat.name as 'nameCat', Type.name as 'nameType', Type.id as idType FROM objectcategory Cat
        INNER JOIN objecttype Type
        ON Type.CategoryID = Cat.id
        ORDER BY Cat.name, Type.name ASC";
$query = mysql_query($sql, $connection);
while ($row = mysql_fetch_array($query)) {
    $test['name'] = $row['nameType'];
    $test['previewFileName'] = $row['idType'].'_'.$row['nameType'];
    $test['id']=$row['idType'];
    $array[$row['nameCat']][]=$test;
}
mysql_close($connection);

global $landscapeMode,$minlon,$minlat,$maxlon,$maxlat,$mlat,$mlon,$zoom,$camx,$camy,$camz,$tarx,$tarz;
if(isset($_GET['mode']))
{
    if(!strcmp($_GET['mode'], 'boundary'))$landscapeMode='boundary';
    if(!strcmp($_GET['mode'], 'zoom'))$landscapeMode='zoom';
    if(!strcmp($_GET['mode'], 'camera'))$landscapeMode='camera';
}
else {
    $landscapeMode='boundary';
}

$minlon=(isset($_GET['minlon'])&& is_numeric($_GET['minlon']))?$_GET['minlon']:-180;
$minlat=(isset($_GET['minlat'])&& is_numeric($_GET['minlat']))?$_GET['minlat']:-90;
$maxlon=(isset($_GET['maxlon'])&& is_numeric($_GET['maxlon']))?$_GET['maxlon']:180;
$maxlat=(isset($_GET['maxlat'])&& is_numeric($_GET['maxlat']))?$_GET['maxlat']:90;
$mlat=(isset($_GET['mlat'])&& is_numeric($_GET['mlat']))?$_GET['mlat']:0;
$mlon=(isset($_GET['mlon'])&& is_numeric($_GET['mlon']))?$_GET['mlon']:0;
$camy=(isset($_GET['camy'])&& is_numeric($_GET['camy']))?$_GET['camy']:0;
$camx=(isset($_GET['camx'])&& is_numeric($_GET['camx']))?$_GET['camx']:0;
$camz=(isset($_GET['camz'])&& is_numeric($_GET['camz']))?$_GET['camz']:0;
$tarx=(isset($_GET['tarx'])&& is_numeric($_GET['tarx']))?$_GET['tarx']:0;
$tarz=(isset($_GET['tarz'])&& is_numeric($_GET['tarz']))?$_GET['tarz']:0;
$zoom=(isset($_GET['zoom'])&& is_numeric($_GET['zoom']))?$_GET['zoom']:0;
?>
<!DOCTYPE html>
<html>
    <head>
        <title>OSMEX3D</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">

        <script type="text/javascript" src="jquery/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="jquery/jquery-ui-1.10.2.custom.min.js"></script>
        <script type="text/javascript" src="jquery/jquery.color.js"></script>
        <script type="text/javascript" src="jquery/jquery.Jcrop.min.js"></script>
        
        <script type="text/javascript" src="scripts/TextureBuilder.prototypes.js"></script>
        <script type="text/javascript" src="scripts/TextureBuilder.js"></script>
        
        <link type="text/css" href="css/smoothness/jquery-ui-1.10.2.custom.min.css" rel="stylesheet" />
        <link type="text/css" href="css/jcrop/jquery.Jcrop.min.css" rel="stylesheet" />
        <link type="text/css" href="css/TextureBuilder.css" rel="stylesheet" />
        <link rel="stylesheet" href="css/main.css" />
        
        <script type="text/javascript">
            <?php
                global $landscapeMode,$minlon,$minlat,$maxlon,$maxlat,$mlat,$mlon,$zoom,$camx,$camy,$camz,$tarx,$tarz;
                echo<<<HERE
                    landscapeMode='$landscapeMode';
                    minlon=$minlon;
                    minlat=$minlat;
                    maxlon=$maxlon;
                    maxlat=$maxlat;
                    mlon=$mlon;
                    mlat=$mlat;
                    zoom=$zoom;
					camx=$camx;
					camz=$camz;
					camy=$camy;
		        	tarx=$tarx;
					tarz=$tarz;
HERE;
            ?>
            var searchbar_template="<div id='searchbar'>\
                <div id='searchbar_header'>\
                    &nbsp;<h6>Search results</h6><a class='close_link' href='#'>Close</a>\
                </div>\
                <div id='searchbar_content'>\
                    <h6>Results from <a href='http://nominatim.openstreetmap.org/'>OpenStreetMap Nominatim</a></h6>\
                    <div id='nominatium'>\
                    </div>\
                    <h6>Results from <a href='http://www.geonames.org/'>GeoNames</a></h6>\
                    <div id='geonames'>\
                    </div>\
                </div>\
             </div>";
            var tabSelected = 1;
            var showButton = 1;
            
            function activateAndRefreshPanel(index)
            {
                var panel=$('#objectEditor > div:not(#searchbar)').eq(index);
                panel.empty();

                $("#objectEditor").tabs("option", "active", -1); // first switching to dummy tab
                $("#objectEditor").tabs("option", "active", index);
            }
            /*function setSlidingHeight(panel)
            {
                    var pictureCount = panel.children(".imgContainer").length;
                    if(pictureCount<4 && pictureCount>0)
                        {panel.css("height", "130");panel.css("overflow-y", "hidden");}
                    else if(pictureCount<7 && pictureCount >=4)
                        {
                            panel.css("height","250");
                            panel.css("overflow-y", "hidden");
                        }
                    else 
                        panel.css("height", "360");
            }*/
            
            $(document).ready(function(){
                $(document).tooltip({
                    items: ".prev",
                    content: function(){
                        var src = $(this).attr("src");
                        var res = src.substring(0, src.length-9);
                        var ending = ".png";
                        res+=ending;
                        return "<img class='fullPicture' src='"+res+"'>";
                    },
                    position: {
                        my: "center+150 bottom",
                        at: "center top"
                    }
                });
//         Making tabs from containers                                              
                $(".accordionContainer").tabs();
                //$("#objectEditor").tabs();

             function resize()
                {
                    //         Calculating height for containers                         
                    var heightObj = $(window).height()*0.97;
                    var widthObj = $(window).width()*0.97;
                    $("#sidebar").css("height", heightObj);
                    $("#content").css("height", heightObj);
                    $(".accordionContainer").css("height", heightObj-180);
                    $("#objectEditor").height($("#content").height() - 8);
                    if(widthObj<=$('#mainContainer').css('min-width').replace('px','')) return;
                    $("#content").css("width", widthObj-$("#sidebar").width()-40);
                    $('iframe').css('width',$('iframe').parent().width()-2);
                    $('iframe').css('height',$('iframe').parent().height());
                }
                resize();
                $(window).resize(function(){
                    resize();
                });
                
//         EVENT HANDLERS
//            1. Event handler for flip
                $(".flip").click(function(){
                    var panel = $(this).next(".slidingPanel");
                    $(this).next(".slidingPanel").slideToggle(500, function(){
                        //setSlidingHeight(panel);
                        $(this).toggleClass("closed");
                    });
                    
                });
//            2. Event handler for search input (sketches tab)
             
                // Work area tabs
                var initializator={
                    tabMap:{
                        url:'ajax/mapView.html',
                        activator:function(){
                            var iframe=this.find('iframe');

                            if(landscapeMode=='camera') 
                                iframe.attr('src','landscape.php?camx='+camx+'&camy='+camy+'&camz='+camz+'&mode='+'camera'+'&tarz='+tarz+'&tarx='+tarx+'&rnd='+Math.random());
							else if(landscapeMode=='zoom') 
                                    iframe.attr('src','landscape.php?zoom='+zoom+'&mlon='+mlon+'&mlat='+mlat+'&mode='+'zoom'+'&rnd='+Math.random());
                            else
                                    iframe.attr('src','landscape.php?minlon='+minlon+'&minlat='+minlat+'&maxlon='+maxlon+'&maxlat='+maxlat+'&mode='+'boundary'+'&rnd='+Math.random());
									
                        }
                    },
                    tabArea:{
                        url:'ajax/areaEditor.html',
                        activator:function(){
                            //$(iframe[0].contentWindow.document).trigger('mouseup');
                        }
                    },
                    tabSketch:{
                        url:'ajax/sketchBuilder.html',
                        activator:function(){
                            //iframe[0].contentWindow.triggerMouseup();
                        }
                    },
                    tabTxt:{
                        url:'ajax/textureBuilder.html',
                        activator:function(){
                            prepareTextureBuilder();
                            $(document).on("addTexture", function(e){
                                for(var i=0;i<e.textures.length;i++)
                                {
                                    $('#txt').append("<div class='imgContainer'>\n\
                                    <img class='prev' src='"+e.textures[i].thumbnail+"'>\
                                    <div class='desc'>"+e.textures[i].name+"</div></div>");
                                }
                            });
                        }
                    }
                };
                $("#objectEditor").tabs({
                    active: -1,   // to trigger beforeActivate for the first tab
                    beforeActivate:function(event, ui){
                        /*$('iframe').each(function(index,element){
                            $(this.contentWindow.document).trigger('mouseup');
                        });*/
                        //$(iframe[0].contentWindow.document).trigger('mouseup');
                        if(ui.newPanel.is(':empty'))
                        {
                            var key=ui.newTab.attr('id');
                            
                            if (key === "dummy") return;
                            
                            $('#loading').show();
                            
                            ui.newPanel.load(initializator[key].url, '', function() {
                                
                                var iframe = ui.newPanel.find('iframe');
                                
                                if (iframe.length) {
                                    
                                    iframe.css("visibility", "hidden");
                                    
                                    iframe.css('width', ui.newPanel.width());
                                    iframe.css('height', ui.newPanel.height());
                                }
                                
                                initializator[key].activator.call(ui.newPanel, event, ui);       
                                
                                if (iframe.length) {

                                    iframe.load(function() {

                                        iframe.css("visibility", "visible");
                                        $('#loading').hide();
                                    });
                                }
                                else {
 
                                    $('#loading').hide();
                                }
                            });
                        }
                    }
                });
                window.refreshAccordion=function()
                {
//                    var mass = [];
                    $.ajax({
                        url:"server_scripts/objSearch.php?q="+$("#accSearch").val(),
                        async: true,
                        cache: false,
                        success:function(result)
                        {
//                            $(".accordion").children(".slidingPanel").each(function(index){
//                                if($(this).hasClass("closed"))
//                                {
//                                    mass[index]=$(this).index();
//                                }   
//                            });
                            $(".accordion").empty();
                            $(".accordion").html(result);
                            //maximizeFlips();
                            $(".slidingPanel").slideDown("fast");
//                            $(".slidingPanel").each(function(){
//                                if($(this).index())
//                                    $(this).css("display", "none");
//                            });
                  //    !  Handlers don't work after clearing the accordion, we need to assign it again
                            $(".flip").click(function(){
                                var panel = $(this).next(".slidingPanel");
                                $(this).next(".slidingPanel").slideToggle(500, function (){
                                    //setSlidingHeight(panel);
                                    $(this).toggleClass("closed");
                                });
                            });   
                            attachImgContainerHandlers();
                        }
                    }); //end of ajax 
                };
                $("#accSearch").keyup(function (){
                    if(tabSelected===1){
                        window.refreshAccordion();
                    }
                    if(tabSelected===2){
                        $.ajax({
                            url:"server_scripts/getTexture.php?mode=search&from=0&to=15&qw="+$("#accSearch").val(),
                            async: true,
                            cache: false,
                            dataType:'json',
                            success:function(result)
                            {
                                var str="";
                                for (i in result){
                                    str+="<div class='imgContainer'>";
                                    str+="<img class='prev' src='"+result[i].thumbnail+"'>";
                                    str+="<div class='desc'>"+result[i].name+"</div></div>";
                                }
                                if(str==="")
                                {
                                    $("#txt").html("<p style='font-size: 1.1em;'>no textures found</p>");
                                }
                                else
                                {
                                    $("#txt").html(str);
                                }
                            }
                        });
                        $("#txt").children(".imgContainer").click(function(){
                            /*$('.imgContainer.clicked').unbind("mouseleave");
                            $('.imgContainer.clicked').unbind("mouseenter");
                            $('.imgContainer.clicked').removeClass('clicked');
                            $(this).addClass('clicked');
                            $('.clicked').bind("mouseleave");
                            $('.clicked').bind("mouseenter");*/
                           
                            if(!$(this).hasClass("clicked"))
                            {
                                $(this).css("border", "1px solid red");
                                $(this).unbind("mouseleave");
                                $(this).unbind("mouseenter");
                            }
                            else
                            {
                                $(this).css("border", "1px solid white");
                                $(this).bind("mouseleave");
                                $(this).bind("mouseenter");
                            }
                            $(this).toggleClass("clicked");
                        });
                        $("#txt").children(".imgContainer").mouseenter(function(){
                            $(this).css("cursor", "pointer");
                            $(this).css("border", "1px solid red");
                        });
                        $("#txt").children(".imgContainer").mouseleave(function(){
                            $(this).css("cursor", "default");
                            $(this).css("border", "1px solid white");
                        });
                    }
                    
                }); //end of search input handler
//            3. Event handler for button "Collapse All"  
                $("#collapseImg").click(function (){
                    var i=0;
                    $(".slidingPanel").each(function(index){
                        if($(this).css("display")==="block")
                            i++;
                    });
                    if(i)
                        $(".slidingPanel").slideUp("fast");
                    else
                        $(".slidingPanel").slideDown("fast");
                });
//            4. Event handler for mode selector            
                $("#mode").change(function (){
                    if($("#mode :selected").val()==="Edit mode")
                        {
                            showButton = 1;
                            $("#tabSketch").css("display","block");
                            $("#tabTxt").css("display","block");
                            $(".accordionContainer").css("display", "block");
                            width=$("#searchDivc").width();
                            $("#searchDivc").width(width+150);
                            $("#sidebar").width(width+150);
                            //$("#content").css("width", "64%");
                            $("#description").css("display", "none");
                            resize();
                            //$('iframe').css('width',$('iframe').parent().width());
                            //$('iframe').css('height',$('iframe').parent().height());
                        }
                    if($("#mode :selected").val()==="View mode")
                        {
                            showButton = 0;
                            $("#tabArea").css("display", "none");
                            $("#tabMap").css("display", "block");
                            $("#tabSketch").css("display","none");
                            $("#tabTxt").css("display","none");
                            $(".accordionContainer").css("display", "none");
                            width=$("#searchDivc").width();
                            $("#searchDivc").width(width-150);
                            $("#sidebar").width(width-150);
                            //$("#content").css("width", "75%");
                            $("#description").css("display", "block");
                            //$('iframe').css('width',$('iframe').parent().width());
                            //$('iframe').css('height',$('iframe').parent().height());
                            resize();
                        }
                        
                        activateAndRefreshPanel(0);
                });
//            5. Submit OSM Search Handler
                $("#osmSearchForm").submit(function(){
                    if($('#searchbar').size()==0)
                    {
                        $("#objectEditor").children('div').css({'margin-left':'250px'});
                        var searchbar=$(searchbar_template);
                        searchbar.insertAfter('#objectEditor ul');
                        $('iframe').css('width',$('iframe').parent().width());
                        $('iframe').css('height',$('iframe').parent().height());
                        //$('iframe')[0].contentWindow.onWindowResize();
                        //$('#searchbar').next().css({'margin-left':'250px'});
                        $(".close_link").click(function(){
                            //$('#searchbar').next().css({'margin-left':'0px'});
                            $("#objectEditor").children('div').css({'margin-left':'0px'});
                            $('#searchbar').remove();
                            $('iframe').css('width',$('iframe').parent().width());
                            $('iframe').css('height',$('iframe').parent().height());
                        });
                    }
                    $("#nominatium").html('<br><center><img align="center" src="img/searching.gif"/></center>');
                    $("#geonames").html('<br><center><img align="center" src="img/searching.gif"/></center>');
                    $.ajax({
                        url:"server_scripts/osmSearch.php?q="+$("#query").val(),
                        async: true,
                        cache: false,
                        dataType:'json',
                        success:function(result){
                            $("#nominatium").html(result['nominatium']);
                            
                            $("#nominatium ul").next().remove();
                            $("#nominatium ul").next().remove();
                            $("#geonames").html(result['geonames']);
                            $("#geonames ul").next().remove();
                            $("#geonames ul").next().remove();
                            $('.search_details').remove();
                            $('.set_position').click(function(){
                                
                                var confirmed = true;
                                
                                //if($("#mode :selected").val()!=="View mode")
                                if ($("#tabArea").is(':visible'))
                                {
                                    confirmed = false;
                                    
                                    var mydialog=$(dialog_template).attr('title','Switch to search result');
                                    mydialog.find('p').append('Are you sure you want to cancel editing and switch to the search result?');
                                    mydialog.dialog({
                                          resizable: false,
                                          height:200,
                                          modal: true,
                                          buttons: {
                                            "Yes": function() {
                                              $( this ).dialog( "close" );
                                              confirmed = true;
                                            },
                                            "No": function() {
                                              $( this ).dialog( "close" );
                                            }
                                        }
                                    });
                                }
                                
                                if (confirmed) {

                                    var link=$(this);

                                    if(link.attr('data-zoom'))
                                    {
                                        landscapeMode='zoom';
                                        minlon=0;
                                        minlat=0;
                                        maxlon=0;
                                        maxlat=0;
                                        mlon=Number(link.attr('data-lon'));
                                        mlat=Number(link.attr('data-lat'));
                                        zoom=Number(link.attr('data-zoom'));
                                    }
                                    else
                                    {
                                        landscapeMode='boundary';
                                        minlon=Number(link.attr('data-min-lon'));
                                        minlat=Number(link.attr('data-min-lat'));
                                        maxlon=Number(link.attr('data-max-lon'));
                                        maxlat=Number(link.attr('data-max-lat'));
                                        mlon=0;
                                        mlat=0;
                                        zoom=0;
                                    }

                                    activateAndRefreshPanel(0);
                                }
                                
                                return false;
                            });
                            //$(result).appendTo('#nominatium');
                        }
                     });
                     return false;
                });
//           6. Image Container handlers
                window.releaseSelection=function ()
                {
                    $(".imgContainer").removeClass('clicked');
                    $('.imgContainer').css("border", "1px solid white");
                }
                function attachImgContainerHandlers()
                {
                    $(".imgContainer").mouseenter(function(){
                        if($(this).hasClass('clicked')) return;
                        $(this).css("cursor", "pointer");
                        $(this).css("border", "1px solid red");
                    });
                    $(".imgContainer").mouseleave(function(){
                        if($(this).hasClass('clicked')) return;
                        $(this).css("cursor", "default");
                        $(this).css("border", "1px solid white");
                    });
                    $(".imgContainer").click(function(){
                        if($(this).hasClass('clicked'))
                        {
                            $('.imgContainer.clicked').css("border", "1px solid white");
                            $('.imgContainer.clicked').removeClass('clicked');
                            if($("#objectEditor").tabs('option','active')==1)
                            {
                                var frame=$("#areaEditorFrame")[0].contentWindow;
                                frame.sketchFactory.stopBuild();
                            }
                            if($("#objectEditor").tabs('option','active')==2)
                            {
                                var frame=$("#sketchBuilderFrame")[0].contentWindow;
                                frame.sketchFactory.stopBuild();
                            }
                            return;
                        }
                        $('.imgContainer.clicked').css("border", "1px solid white");
                        $('.imgContainer.clicked').removeClass('clicked');
                        $(this).addClass('clicked');
                        if($("#objectEditor").tabs('option','active')==1)
                        {
                            var frame=$("#areaEditorFrame")[0].contentWindow;
                            frame.sketchFactory.startBuild($(this).attr('id'));
                        }
                        if($("#objectEditor").tabs('option','active')==2)
                        {
                            var frame=$("#sketchBuilderFrame")[0].contentWindow;
                            frame.sketchFactory.startBuild($(this).attr('id'));
                        }
                    });
                }
                attachImgContainerHandlers();
                
//           7. Search handler
                $("#sketchTab").click(function (){
                    tabSelected = 1;
                     $("#collapseImg").css("display","block");
                });
                $("#txtTab").click(function(){
                    tabSelected = 2;
                    $("#collapseImg").css("display","none");
                    $.ajax({
                            url:"server_scripts/getTexture.php?mode=thumbnails&from=0&to=15&qw=f",
                            async: false,
                            cache: false,
                            dataType:'json',
                            success:function(result)
                            {
                                var str="";
                                for (i in result){
                                    str+="<div class='imgContainer'>";
                                    str+="<img class='prev' src='"+result[i].thumbnail+"'>";
                                    str+="<div class='desc'>"+result[i].name+"</div></div>";
                                }
                                $("#txt").html(str);
                                
                            }
                        });
                        $("#txt").children(".imgContainer").click(function(){
                            if(!$(this).hasClass("clicked"))
                            {
                                $(this).css("border", "1px solid red");
                                $(this).unbind("mouseleave");
                                $(this).unbind("mouseenter");
                            }
                            else
                            {
                                $(this).css("border", "1px solid white");
                                $(this).bind("mouseleave");
                                $(this).bind("mouseenter");
                            }
                            $(this).toggleClass("clicked");
                        });
                        $("#txt").children(".imgContainer").mouseenter(function(){
                            $(this).css("cursor", "pointer");
                            $(this).css("border", "1px solid red");
                        });
                        $("#txt").children(".imgContainer").mouseleave(function(){
                            $(this).css("cursor", "default");
                            $(this).css("border", "1px solid white");
                        });
                });                
//               END OF EVENT HANDLERS   
//            Setting default mode to view mode 
                
                
                
                $("#tabArea").css("display", "none");
                
                $("#tabSketch").css("display","block");
                $("#tabTxt").css("display","block");
                $(".accordionContainer").css("display", "block");
                //width=$("#searchDivc").width();
                //$("#searchDivc").width(width+150);
                //$("#sidebar").width(width+150);
                //$("#content").css("width", "64%");
                //var searchbar=$(searchbar_template);
                //searchbar.insertAfter('#objectEditor ul');
                activateAndRefreshPanel(0);
                //maximizeFlips();
                //
                //$("#objectEditor").tabs({active:0});
            });
            function disableMapEditing() {
            
                parent.$("#tabArea").css("display", "none");
                parent.$("#tabMap").css("display", "block");
                
                parent.activateAndRefreshPanel(0);
            }
            function enableMapEditing() {
                                
                parent.$("#tabArea").css("display", "block");                
                parent.$("#tabMap").css("display", "none");
                
                parent.activateAndRefreshPanel(1);
            }
        </script>
    </head>
        <body>
            <div id="mainContainer">
                <div id="sidebar">
		    <div id="logo"><p align="center"><img src="img/logo.png" height="87" width="265"></p></div>
                    <div id="searchDivc">
                    <div id="osmSearch">
                        <form id="osmSearchForm">
                            <input name="commit" type="submit" value="Go">
                            <input autofocus="autofocus" id="query" name="query" placeholder="Search" tabindex="1" type="text" value="">
                        </form>
                    </div>
                    </div>
                        <p id="description">This open source project is created to extend OpenStreetMap to 3D.</p>
                    <div class="accordionContainer">
                        <ul>
                        <li><a id="sketchTab" href="#acc">Sketches</a></li>
                        <li><a id="txtTab" href="#txt">Textures</a></li>
                        </ul>
                        <img id="collapseImg" src="img/collapse.png">
                        <input id="accSearch" type="search" placeholder="Start typing a name here...">
                        <div id="acc" class="accordion ui-widget ui-widget-content ui-corner-all">
                            <?php
                            global $array;
                            foreach ($array as $nameFigureType => $instances) {
                                echo '<div objectcategory="'.$nameFigureType.'" class="flip ui-widget ui-widget-header ui-corner-all">'.$nameFigureType.'('.sizeof($instances).')</div>';                           
                                echo '<div class="slidingPanel ui-widget ui-widget-content ui-corner-all">';
                                
                                for($i=0;$i<sizeof($instances);$i++)
                                {
                                    echo '<div class="imgContainer" id="'.$instances[$i]['id'].'">';
                                    echo '<img class="prev" src="previews/'.$instances[$i]['previewFileName'].'_mini.png">';
                                    echo '<div class="desc">';
                                    echo $instances[$i]['name'];
                                    echo '</div></div>';
                                }
                                echo '</div>';   
                                echo '<div class="scrollHelper"></div>';
                            }
                            ?>
                        </div>
                        <div id="txt" class="accordion ui-widget ui-widget-content ui-corner-all">
                        </div>
                    </div>
                </div>
                <div id="content">
                    <div id="objectEditor">
                        <ul>
                            <li id="tabMap"><a href="#map">Map</a></li>
                            <li id="tabArea"><a href="#areaEditor">Map(EDIT)</a></li>
                            <li id="tabSketch"><a href="#sketchBuilder">Sketch Builder</a></li>
                            <li id="tabTxt"><a href="#txtBuilder">Texture Builder</a></li>
                            <li id="dummy" style="display:none;"><a href="#txtBuilder">dummy</a></li>
                        </ul>
                        <select id="mode" size="1">
                            <option value="View mode">
                                View Mode
                            </option>
                            <option selected value="Edit mode">
                                Edit Mode
                            </option>
                        </select>
                        <div class="panel_body" id="map"></div>
                        <div class="panel_body" id="areaEditor"></div>
                        <div class="panel_body" id="sketchBuilder"></div>
                        <div class="panel_body" id="txtBuilder"></div>
                        <div id="loading" style="display:none;"><img src="img/loading.gif" style="position:absolute;top:50%;left:50%;margin-left:-16px;margin-top:-16px;width:32px;height:32px;"/></div>
                    </div>
                </div>
            </div>
    </body>
</html>
