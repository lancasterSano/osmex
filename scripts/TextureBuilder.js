var msg_cant_add_point="Can't add new point because this action will cause lines intersection.";
var msg_cant_move_point="Can't move to this location because this action will cause lines intersection.";
var msg_cant_connect="Can't connect firts and last point because this action will cause lines intersection.";
var msg_cant_complete="Can't close selection. Region must contain at least 3 points.";
var msg_cant_remove="Can't remove this point because this action will cause lines intersection.";
var title_new_region_discard_changes="New selection area";
var msg_new_region_discard_changes="Do you want to create new selection area? Current unclosed area will be lost.";
var title_discard_changes_onselect="Unclosed area";
var msg_discard_changes_onselect="Do you want to discard changes and edit another selection?";
var title_remove_region="Remove region";
var msg_remove_region="Are you shure you want to remove this region?";
var title_clear_all="Clear all";
var msg_clear_all="Are you shure you want to clear all regions?";
var dialog_template="<div id='dialog-confirm'>\
  <p><span class='ui-icon ui-icon-alert' style='float: left; margin: 0 7px 20px 0;'></span></p>\
</div>";

var red_alert_template="<div style='position:absolute;top:5px; left:5px; z-index: 999; width:300px; display:none;' id='message' class='ui-widget'>\
	<div class='ui-state-error ui-corner-all' style='padding: 0 .7em;'>\
		<p>\
                    <span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>\
                    <p id='msgtext'></p>\
                </p>\
	</div>\
</div>";

var info_alert_template="<div style='position:absolute;top:5px; left:5px; z-index: 999; width:300px; display:none;' class='ui-widget' id='tip'>\
            <div class='ui-state-highlight ui-corner-all' style='margin-top: 20px; padding: 0 .7em;'>\
                <p><span class='ui-icon ui-icon-info' style='float: left; margin-right: .3em;'></span>\
                        <p id='msgtext'></p>\
                </p>\
            </div>\
	</div>";

var region_item_template="<li style='height:30px;padding:5px' class='region-item ui-widget ui-widget-content ui-corner-all'>\
			<input type='text' size='25' style='float:left'>\
			<button class='remove-region' style='float:right;'>Remove</button>\
		</li>";
var init_template="<div id='drop' class='drop-zone'>\
	<output id='filelist'></output>\
	<div class='ui-widget' id='tip'>\
            <div class='ui-state-highlight ui-corner-all' style='margin-top: 20px; padding: 0 .7em;'>\
                <p><span class='ui-icon ui-icon-info' style='float: left; margin-right: .3em;'></span>\
                        Please, drag your image here.\
                </p>\
            </div>\
	</div>\
</div>";

function prepareTextureBuilder()
{
var mouseX=0;
var mouseY=0;
var ie=false;
var mycanvas=null;
var sourceImage=null;
var jcropInstance=null;

var currentIndex=0;
var regions=[];
var regionsPoints=[];

var alert={};
var timeout_id=0;

function hideMessage()
{   
    if(alert.fadeOut)
        alert.fadeOut(1000,function(){});
}

function showInfoMessage(text)
{
    clearTimeout(timeout_id);
    if(alert.remove)
        alert.remove();
    alert=$(info_alert_template);
    alert.find("#msgtext").html(text);
    alert.appendTo('#textureBuilder');
    //$("#msgtext").html(text);
    alert.fadeIn(600,function(){
       timeout_id=setTimeout(hideMessage, 4400); 
    });
}

function showErrorMessage(text)
{
    clearTimeout(timeout_id);
    if(alert.remove)
        alert.remove();
    alert=$(red_alert_template);
    alert.find("#msgtext").html(text);
    alert.appendTo('#textureBuilder');
    //$("#msgtext").html(text);
    alert.fadeIn(600,function(){
       timeout_id=setTimeout(hideMessage, 4400); 
    });
}
function relMouseCoords(event){
    var totalOffsetX = 0;
    var totalOffsetY = 0;
    var canvasX = 0;
    var canvasY = 0;
    var currentElement = this;

    do{
        totalOffsetX += currentElement.offsetLeft - currentElement.scrollLeft;
        totalOffsetY += currentElement.offsetTop - currentElement.scrollTop;
    }
    while(currentElement = currentElement.offsetParent)

    canvasX = event.pageX - totalOffsetX;
    canvasY = event.pageY - totalOffsetY;

    return {x:canvasX, y:canvasY}
}
function getMouseXY(e)
{
  //if (ie) 
  //{
	  mouseX = e.clientX; /*+ document.body.parentElement.scrollLeft;*/
	  mouseY = e.clientY; /*+ document.body.parentElement.scrollTop;*/
  /*} else { 
    mouseX = e.pageX
    mouseY = e.pageY
  } */ 
  /*var coords=relMouseCoords(e);
  mouseX=coords.x;
  mouseY=coords.y;*/
  return true;
}
function drawPoint(e)
{
    //var mouseX=e.clientX;
    //var mouseY=e.clientY;
    hideMessage();
    if(regions[currentIndex].addPoint)
        regions[currentIndex].addPoint(mouseX-$('#drop').offset().left, mouseY-$('#drop').offset().top);
        //regions[currentIndex].addPoint(mouseX-$('.jcrop-holder').position().left, mouseY-$('.jcrop-holder').position().top);
}
function onPolygonStatusChanged(index)
{
    if($(".region-item").size()==0 && this.completed)
    {
        $(".region-item").removeClass('ui-selected');
        this.regionItem.addClass('ui-selected');
        this.regionItem.appendTo('#regions');
        return;
    }
    if($(".region-item").size()==1 && !this.completed)
    {
        this.regionItem.remove();
        this.attachRegionItem(region_item_template);
        return;
    }
}
function onSelectRegionItem(index)
{
    if(index==currentIndex) return;
    var r=this;
    function continueSelect(region,index)
    {
        //clearCanvas();
        if(regions[currentIndex])
            regions[currentIndex].hide();
        $('.region-item').removeClass('ui-selected');
        region.regionItem.addClass('ui-selected');
        currentIndex=index;
        region.redraw(true);
    }
    if(regions[currentIndex])
        if(!regions[currentIndex].completed)
        {
            var mydialog=$(dialog_template).attr('title',title_discard_changes_onselect);
            mydialog.find('p').append(msg_discard_changes_onselect);
            mydialog.dialog({
                resizable: false,
                  height:140,
                  modal: true,
                  buttons: {
                    "Yes": function() {
                      regions[currentIndex].revert();
                      continueSelect(r, index);
                      $( this ).dialog( "close" );
                    },
                    "No": function() {
                      $( this ).dialog( "close" );
                    }
                }
            });
        }
        else
        {
            continueSelect(this, index);
        }
    else
        continueSelect(this, index);
}
function updateIndexes()
{
    for(var i=0;i<regions.length;i++)
        regions[i].index=i;
}
function onRegionRemove(r)
{
    var mydialog=$(dialog_template).attr('title',title_remove_region);
        mydialog.find('p').append(msg_remove_region);
        
        mydialog.dialog({
            resizable: false,
              height:140,
              modal: true,
              buttons: {
                "Yes": function() {
                  var nindex=r.index;
                  r.hide();
                  r.regionItem.remove();
                  r.unset();
                  delete regions[r.index];
                  regions.splice(r.index,1);
                  delete regionsPoints[r.index];
                  regionsPoints.splice(r.index,1);
                  //currentIndex=(nindex-1>=0)?nindex-1:0;
                  if(regions.length!=0)
                  {
                      //currentIndex=0;
                      regions[0].regionItem.trigger('click');
                  }
                  
                  $( this ).dialog( "close" );
                },
                "No": function() {
                  
                  $( this ).dialog( "close" );
                },
                "Cancel": function() {
                  
                  $( this ).dialog( "close" );
                }
            }
        });
}
function onPolygonRemove(index,fromRevert)
{
    var r=this;
    if(fromRevert)
    {
        this.hide();
        this.regionItem.remove();
        this.unset();
        delete regions[index];
        regions.splice(index,1);
        delete regionsPoints[index];
        regionsPoints.splice(index,1);
        //updateIndexes();
        //clearCanvas();
        //if(regions.length>0)
        //    regions[0].regionItem.trigger('click');
    }
    else
    {
        onRegionRemove(r);
    }
}
function clearCanvas()
{
     $(".point").remove();
     var g=mycanvas[0].getContext("2d");
     g.clearRect(0,0,g.canvas.width,g.canvas.height);
     g.drawImage(sourceImage,0,0);
}
function onPolygonSelectionClick()
{
    hideMessage();
    //$("#drop").unbind('click');
    $('.jcrop-holder').unbind('click', msgSelectionType);
    $('.jcrop-holder').unbind('click', drawPoint);
    //mycanvas.unbind('mousemove', getMouseXY);
    function continueInit()
    {
        //mycanvas.unbind('click');
        //mycanvas.unbind('mousemove');
        //mycanvas.mousemove(getMouseXY);
        $('.jcrop-holder').click(drawPoint);
        if(regions.length>1)
        {
            $(".region-item").removeClass('ui-selected');
            regions[currentIndex].regionItem.addClass('ui-selected');
            regions[currentIndex].regionItem.appendTo("#regions");
        }
    }
    if(regions.length!=0 && !regions[currentIndex].completed)
    {
        var mydialog=$(dialog_template).attr('title',title_new_region_discard_changes);
        mydialog.find('p').append(msg_new_region_discard_changes);
        
        mydialog.dialog({
            resizable: false,
              height:140,
              modal: true,
              buttons: {
                "Yes": function() {
                  regions[currentIndex].clear();
                  continueInit();
                  
                  $( this ).dialog( "close" );
                },
                "No": function() {
                  
                  $( this ).dialog( "close" );
                }
            }
        });
    }
    else
    {
        //clearCanvas();
        if(regions[currentIndex])
            regions[currentIndex].hide();
        //regions[currentIndex].hide();
        currentIndex=regions.length;
        regionsPoints[regionsPoints.length]=[];
        regions[regions.length]=new PolygonRegion(regionsPoints[currentIndex],currentIndex);
        regions[currentIndex].attachCanvas(mycanvas[0]);
        regions[currentIndex].attachHandlesContainer($('#drop'));
        regions[currentIndex].attachImage(sourceImage);
        regions[currentIndex].errorOccurred(showErrorMessage);
        regions[currentIndex].hideMessage(hideMessage);
        regions[currentIndex].statusChanged(onPolygonStatusChanged);
        regions[currentIndex].attachRegionItem(region_item_template);
        regions[currentIndex].remove(onPolygonRemove);
        regions[currentIndex].select(onSelectRegionItem);
        continueInit();
    }
}
function onRectangleSelectionClick()
{
    hideMessage();
    $('.jcrop-holder').unbind('click', msgSelectionType);
    $('.jcrop-holder').unbind('click', drawPoint);
    //mycanvas.unbind('mousemove', getMouseXY);
    //$("#drop").unbind('click');
    function continueInit()
    {
        //mycanvas.unbind('click');
        //mycanvas.unbind('mousemove');
        //mycanvas.mousemove(getMouseXY);
        //mycanvas.click(drawPoint);
        //if(regions.length>1)
        //{
        if(regions[currentIndex].type!=='rectangle')
        {
            regions[currentIndex].hide();
            regions[currentIndex].regionItem.remove();
            regions[currentIndex].unset();
            //delete regions[currentIndex];
            //regions.splice(currentIndex,1);
            //delete regionsPoints[currentIndex];
            //regionsPoints.splice(currentIndex,1);
            regions[currentIndex]={};
            regionsPoints[currentIndex].length=0;
            regions[currentIndex]=new RectangleRegion(regionsPoints[currentIndex],currentIndex);
            regions[currentIndex].attachJcrop(jcropInstance);
            regions[currentIndex].attachRegionItem(region_item_template);
            regions[currentIndex].remove(function(){
                onRegionRemove.call(this,this);
            });
            regions[currentIndex].select(onSelectRegionItem);
        }
        $(".region-item").removeClass('ui-selected');
        regions[currentIndex].regionItem.addClass('ui-selected');
        regions[currentIndex].regionItem.appendTo("#regions");
        //}
    }
    if(regions.length!=0 && !regions[currentIndex].completed)
    {
        var mydialog=$(dialog_template).attr('title',title_new_region_discard_changes);
        mydialog.find('p').append(msg_new_region_discard_changes);
        
        mydialog.dialog({
            resizable: false,
              height:140,
              modal: true,
              buttons: {
                "Yes": function() {
                  regions[currentIndex].clear();
                  continueInit();
                  
                  $( this ).dialog( "close" );
                },
                "No": function() {
                  
                  $( this ).dialog( "close" );
                }
            }
        });
    }
    else
    {
        if(regions[currentIndex])
            regions[currentIndex].hide();
     
        currentIndex=regions.length;
        regionsPoints[regionsPoints.length]=[];
        regions[regions.length]=new RectangleRegion(regionsPoints[currentIndex],currentIndex);
        regions[currentIndex].attachJcrop(jcropInstance);
        regions[currentIndex].attachRegionItem(region_item_template);
        regions[currentIndex].remove(function(){
                onRegionRemove.call(this,this);
        });
        regions[currentIndex].select(onSelectRegionItem);
        continueInit();
    }
}
function msgSelectionType()
{
    showErrorMessage('Please, choose selection type first');
}
function initCanvas(e)
{
    $("#tip").remove();
    var img=new Image();
    img.src=e.target.result;
    img.onload=function()
    {
        $("#drop").html("<canvas class='image' id='canvas'/>");
        //$("#canvas").css({'width':img.width,'height':img.height});
        $("#canvas").attr('width',img.width);
        $("#canvas").attr('height',img.height);
        $("#drop").css({
            'padding':'0px'
        });
        $("#drop").css({
            'width':img.width,
            'height':img.height
        });
        mycanvas=$("#canvas");
        sourceImage=img;
        g=mycanvas[0].getContext("2d");
        g.drawImage(img,0,0);
        //mycanvas.mousemove(getMouseXY);
        //mycanvas.click(drawPoint);
        //mycanvas[0].oncontextmenu=function(){
        //    return false;
        //}
        $("#polygon-selection").button("enable");//.click(onPolygonSelectionClick);
        $("#rectangle-selection").button("enable");//.click(onRectangleSelectionClick);
        $("#clear-all").button("enable");
        $("#process-all").button("enable");
        var options={allowSelect: false};
        $("#drop").Jcrop(options,function(){
            jcropInstance=this;
            jcropInstance.release();
            $(".jcrop-holder").click(msgSelectionType);
        });
        $('.jcrop-holder')[0].oncontextmenu=function(){
            return false;
        }
        /*
        currentRegion=new PolygonRegion();
        currentRegion.attachCanvas(mycanvas[0]);
        currentRegion.attachHandlesContainer($("#drop"));
        currentRegion.attachImage(sourceImage);
        currentRegion.errorOccurred(showErrorMessage);
        currentRegion.hideMessage(hideMessage);
        currentRegion.statusChanged(function(){
            alert('Status changed!');
        });
        */
            //addNewRegion();
    }
    //canvasDiv.onclick=drawPoint;
}


function handleFileSelect(evt) {
    evt.stopPropagation();
    evt.preventDefault();

    var files = evt.dataTransfer.files; // FileList object.

    // files is a FileList of File objects. List some properties.
    var output = [];
    for (var i = 0, f; f = files[i]; i++) 
	{
		if (!f.type.match('image.*')) 
		{
			continue;
		}
		var reader = new FileReader();
		// Read in the image file as a data URL.
		reader.readAsDataURL(f);
		reader.onload = (function(theFile) {
        return function(e) {

          initCanvas(e);

        };
      })(f);
    }
  }

  function handleDragOver(evt) {
    evt.stopPropagation();
    evt.preventDefault();
    evt.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
  }
function send(pack)
{
    var result={};
    //var response={};
    $.ajax({
        async:false,
        type:'POST',
        url:'server_scripts/textureSaver.php',
        processData: false,
        headers: {
            'Content-Type': 'application/json'
        },
        data:pack,
        dataType:'json',
        success:function(r)
        {
            result=r;
            if(result.success)
            {
                $.event.trigger({
                    type: "addTexture",
                    textures:result['textures']
                });
            }
        },
        error:function()
        {
            result.message="Sorry, can't save your textures."
            result.success=false;
        }
        
    });
    return result;
}
function initTextureEditor()
{
	if(document.all)
		ie=true;
        $('#radio').buttonset();
        //$("#radio").button("disable")
        $("#polygon-selection").button("disable").click(onPolygonSelectionClick);//.click(function(){});
        $("#rectangle-selection").button("disable").click(onRectangleSelectionClick);//.click(function(){});
        $("#clear-all").button({disabled:true}).click(function(){
            function continueClear()
            {
                if(regions[currentIndex])
                    regions[currentIndex].hide();
                Region.prototype.regions.length=0;
                RectangleRegion.prototype.jcropInstance=null;
                regions.length=0;
                regionsPoints.length=0;
                $('.region-item').remove();
                var drop_zone=$(init_template);
                jcropInstance.destroy();
                //drop_zone.attr('style', '');
                drop_zone.prependTo('#textureBuilder');
                //var dropZone = document.getElementById('drop');
		drop_zone[0].addEventListener('dragover', handleDragOver, false);
		drop_zone[0].addEventListener('drop', handleFileSelect, false);
                $("#polygon-selection").button("disable");
                $("#rectangle-selection").button("disable");
                $("#clear-all").button("disable");
                $("#process-all").button("disable");
                $('.ui-state-active').attr('aria-pressed','false');
                $('.ui-state-active').removeClass('ui-state-active');
                //$(".jcrop-holder").click(msgSelectionType);
            }
            if(regions.length>0)
            {
                var mydialog=$(dialog_template).attr('title',title_clear_all);
                mydialog.find('p').append(msg_clear_all);
                
                mydialog.dialog({
                    resizable: false,
                      height:140,
                      modal: true,
                      buttons: {
                        "Yes": function() {
                          continueClear();
                          
                          $( this ).dialog( "close" );
                        },
                        "No": function() {
                          
                          $( this ).dialog( "close" );
                        }
                    }
                });
                return;
            }
            continueClear();
        });//.click(function(){});
        $("#process-all").button({disabled:true}).click(function(){
            var pack="[";
            var flag=true;
            var result;
            for(var i=0;i<regions.length;i++)
            {
                if(!regions[i].completed)
                {
                    showErrorMessage('Please, complete region #'+(i+1)+'.');
                    return;
                }
                var result=regions[i].getRegion(sourceImage);
                if(result.name==='' || result.name.length>254)
                {
                    showErrorMessage('Please, enter valid name for region #'+(i+1)+'.');
                    return;
                }
                
                var jsonString=JSON.stringify(result);
                if(pack.length+jsonString.length<18*1024*1024)
                {
                    if(i!=0&& pack.length>1) pack+=',';
                    pack+=jsonString;
                }
                else
                {
                    pack+="]";
                    var result=send(pack);
                    if(!result.success)
                    {
                        flag=false;
                        break;
                    }
                    pack="[";
                }
                //image.appendTo('body');
            }
            if(pack.length>1)
            {
                pack+="]";
                result=send(pack);
                if(!result.success)
                {
                    flag=false;
                }
            }
            if(flag===false)
                showErrorMessage(result.message+" Saving failed.");
            else
            {
                regions[currentIndex].hide();
                Region.prototype.regions.length=0;
                regions.length=0;
                regionsPoints.length=0;
                $('.region-item').remove();
                jcropInstance.release();
                $(".jcrop-holder").unbind('click');
                $(".jcrop-holder").click(msgSelectionType);
                showInfoMessage(result.message);
                $('.ui-state-active').attr('aria-pressed','false');
                $('.ui-state-active').removeClass('ui-state-active');
            }
        });
	/*$("#new-selection").button({icons: {
				primary: "ui-icon-circle-plus"
			}}).click(onNewSelectionClick);*/
        // Check for the various File API support.
	if (window.File && window.FileReader && window.FileList && window.Blob) {
		// Great success! All the File APIs are supported.
		//$('body').append("Supported");
		var dropZone = document.getElementById('drop');
		dropZone.addEventListener('dragover', handleDragOver, false);
		dropZone.addEventListener('drop', handleFileSelect, false);

	} else {
                showErrorMessage('The File APIs are not fully supported in this browser.');
	}
}
    //var divp=$('<div>&nbsp;</div>').appendTo('body');
    //divp.css({'background-color':'red','border':'1px #000000 solid','position':'absolute','z-index':'999','width':'6px','height':'6px'});
    $("#textureBuilder").mousemove(function(e){
        //alert(e);
       getMouseXY(e);
       //divp.css('left',e.clientX+5+'px');  
       //divp.css('top',e.clientY+5+'px'); 
    });
    initTextureEditor();
    $("#textureBuilder").bind("dragstart", function(e) {
        if (e.target.nodeName.toUpperCase() == "IMG") {
            return false;
        }
    });
}