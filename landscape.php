<!DOCTYPE html>
<html lang="en">
	<head>
		<title>OSMEX3D MapViewer</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
                <script type="text/javascript" src="jquery/jquery-1.9.1.js"></script>
                <script type="text/javascript" src="jquery/jquery-ui-1.10.2.custom.min.js"></script>
                <script src="threejs/three.min.js"></script>
                <script src="scripts/Camera.js"></script>
                <script src="scripts/CameraController.js"></script>
                <script src="scripts/AreaSelector.js"></script>
                <script src="scripts/Detector.js"></script>
                <script type="text/javascript" src="scripts/AjaxReqForLandscape.js"></script> 
                <script src="scripts/AjaxRequests.js"></script> 
                <script src="scripts/BoundingBox.js"></script>		
                <script src="scripts/Block.js"></script>
                <script src="scripts/SketchType.js"></script>
                <script src="scripts/SketchFactory.js"></script>	
                
                <link type="text/css" href="css/smoothness/jquery-ui-1.10.2.custom.min.css" rel="stylesheet" />
		<style>
			body {
				color: #000;
				font-family:Monospace;
				font-size:13px;
				text-align:center;
				font-weight: bold;

				background-color: white;
				margin: 0px;
				overflow: hidden;
			}
                        .slider_place
                        {
                            position:absolute;
                            top: 10px;
                            right:20px;
                            width:120px;
                            height:50px;
                            z-index:2;
                        }
                        #slider
                        {
                            position:relative;
                            top:4px;
                            left:15px;
                            width:88px;
                        }
                        .opc
                        {
                            margin:0;
                            padding: 0;
                            font-size:10px;
                            font-weight: normal;
                        }
                        .lbl1
                        {
                            margin:0;
                            padding: 0;
                            font-size:10px;
                            font-weight: normal;
                        }
                        #edit_button
                        {
                            position: absolute;
                            top:70px;
                            right:30px;
                            width:100px;
                            height:40px;
                            background-image: url('img/edit.png');
                        }
                        #edit_button.disabled
                        {
                            background-image: url('img/edit_disabled.png');
                        }
                        #edit_button.hover
                        {
                            background-image: url('img/edit_hovered.png');
                        }
                        #edit_button.selected
                        {
                            background-image: url('img/edit_pressed.png');
                        }
		</style>
	</head>

	<body>
        <?php
		
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
echo<<<HERE
<script type="text/javascript">
    
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
    
</script>
HERE;
?>
            <div id="map-controls">
                <div id="edit_button"></div>
                <div class="slider_place ui-widget ui-widget-content ui-corner-all">
                    <p class='opc'>Buildings opacity</p>
                    <p class='lbl1' id="opacity_value">100%</p>
                    <div id="slider">&nbsp;</div>
                </div>
            </div>
        <div  jstcache="0"  id="cont" ></div>
        <div  jstcache="0"  id="build"></div>
		<div  jstcache="0"  id="container"></div>

<script type="text/javascript">
//Class of tile	
function Tile () {
    this.id;
	this.refcount=-1;
	this.tex_x;
	this.tex_z;
	this.lvl;//level
    this.childs = new Array();//4 id of descendants 
    this.childs[0]=-1;
    this.childs[1]=-1;
    this.childs[2]=-1;
	this.childs[3]=-1;
    this.prnt;//parent
	this.texExist=false;
	this.texture;
	this.triangleGeometry = new THREE.Geometry();
	this.destroy = function () {
         delete this.id;
		 delete this.refcount;
		 delete this.tex;
		 delete this.lvl;
		 this.childs.length = 0;delete this.childs;this.childs=null;
		 delete this.prnt;
		 this.triangleGeometry.dispose();
		 //this.texture.dispose();
		 delete this.triangleGeometry;this.triangleGeometry=null;
    };

}
//Class Building
function TileBlds () {
    this.id;//id of tile
	this.x;
	this.z;
	this.cenx;
	this.cenz;
	this.scale_x;
	this.scale_z;
	this.minlon;
	this.minlat;
    this.arrIndxsBlds = new Array();
	this.destroy = function () {
         delete this.id;
		 delete this.scale_x;
		 delete this.scale_z;
		 delete this.minlon;
		 delete this.minlat;
		 delete this.x;
		 delete this.z;
		 delete this.cenx;
		 delete this.cenz;
		delete this.arrIndxsBlds
		this.arrIndxsBlds=null;
    };

}


			if ( ! Detector.webgl ) Detector.addGetWebGLMessage();
                        
                        // instead of stop propagation and prevent default
                        document.onselectstart=function(){return false;};
                        
			var container;
			
			var objectLight;

			var arrCurRoot = new Array();
			var arrCurBld = new Array();
			var curBldId = -1;
			var arrTile = new Array();
			var arrTileBlds = new Array();
			var sketchFactory;
                        var buildingsMaterial;

			var timerid=0;
			var timer=1;
			var initTiles = new Array();
			var initTilesIndx=0;
			var initTileslvl=18;
			var initReady=true;
			var Exist1stTl=false;
			var UnitToPixelScale;
			var tileSizeRoot=40077413.5808;// in [m]
			var lvlbldactive=17;//-1;
			var maxidinque=-1;
			var distfor17=-1;

			var camera, cameraController, scene, renderer;
			var maxAnisotropy;
                        
                        var areaSelector;
                        
                        var mouse = new THREE.Vector2(0, 0);

			var texture;

			var cross;

			var triangleMesh = new Array();
			var MeshOfBlds = new Array();
			var arrTex = new Array();

			var bverify=false;

                        buildingsOpacity=100;
                        
                        $(document).ready(function(){
                            
                       // preload images
                        
			preload(['img/edit.png','img/edit_disabled.png','img/edit_hovered.png','img/edit_pressed.png']);
			 
			function preload(images) {
				var div = document.createElement("div");
				var s = div.style;
				s.position = "absolute";
				s.top = s.left = 0;
				s.visibility = "hidden";
				document.body.appendChild(div);
				div.innerHTML = "<img src=\"" + images.join("\" /><img src=\"") + "\" />";
			}
                            
                            
                            $('#slider').slider({
                                max:100,
                                min:0,
                                value:100,
                                step:1,
                                slide:function(event,ui)
                                {
                                    buildingsOpacity=ui.value;
                                    $("#opacity_value").text(buildingsOpacity + "%");
                                    
                                    buildingsMaterial.opacity = buildingsOpacity / 100.0;
                                    
                                    /*scene.traverse( function( object ) {
                                        
                                        if (object instanceof OSMEX.Block) {
                                            
                                            object.material.transparent = true;
                                            object.material.opacity = buildingsOpacity / 100.0; 
                                        }
                                    });*/
                                }
                            });
                            if (parent.showButton == 1){
                            	$("#edit_button").show();
                            } else {
                            	$("#edit_button").hide();
                            }
                            $('#map-controls').mouseenter(function(){
                                cameraController.enabled=false;
                                document.removeEventListener('mousemove', onDocumentMouseMove, false);
                                document.removeEventListener('mousedown', onDocumentMouseDown, false);
                                document.removeEventListener('mouseup', onDocumentMouseUp, false);
                                //camera.noRotate=true;
                            });
                            $('#map-controls').mouseleave(function(){
                                cameraController.enabled=true;
                                document.addEventListener('mousemove', onDocumentMouseMove, false);
                                document.addEventListener('mousedown', onDocumentMouseDown, false);
                                document.addEventListener('mouseup', onDocumentMouseUp, false);
                                //camera.noRotate=false;
                            });
                            
                                $("#edit_button").click(function(event){
                                    
                                    if ($(this).hasClass('disabled') === false) {

                                        if ($(this).hasClass('selected')) {
                                            areaSelector.stopSelecting();
                                        } else {
                                            areaSelector.startSelecting();
                                        }
                                    }
                            	});
                                
                                $('#edit_button').mouseover(function(){
                                    if ($(this).hasClass('disabled') === false) $(this).addClass('hover');
                                });
                                
                                $('#edit_button').mouseout(function(){
                                    if ($(this).hasClass('disabled') === false) $(this).removeClass('hover');
                                });
                            
                            init();
			    animate();
                        });
                        



//Object ( dynamically add the necessary tiles)

var TLoad = new function () {
    this.maxid=9999999999999;
	//set 1st coordinates for 1st tileRoots
	this.startX=-20038706.7904;
	this.startZ=-20038706.7904;
	this.stepGrid=(Math.abs(this.startX)*2)/4;
	this.idforloadroot=-1;
	this.ReadyForRoot=true;
    this.indx=0;
	this.indxCube=0;
    this.ready=true;                 //a flag of readiness
    this.arTileForAdd = new Array(); //the queue of  tiles for loading
	this.arTileCubeForAdd = new Array(); //the queue of  tiles for loading
	this.requestBld=true;

this.prepareRootID = function (rootid) {
      if(this.ReadyForRoot){
	    this.idforloadroot=rootid;
		this.ReadyForRoot=false;
		this.arTileForAdd.push(rootid);
	       }
    };	

	   // check the queue overflow
this.isFull = function () {
      if(this.arTileForAdd.length>=256)return true;//queue consist of 256 
	  else{return false;}
    };

	  //check tile on present in the queue	  
this.tileinQueue= function (IdTile) {           
      if(this.arTileForAdd.indexOf(IdTile)>=0)return true;
	  else{return false;}
    };

this.tileCubeinQueue= function (strXspaceZ) {           
      if(this.arTileCubeForAdd.indexOf(strXspaceZ)>=0)return true;
	  else{return false;}
    };

	  //add tile in queue
this.pushTile = function (IdTile) {              
    if(!this.tileinQueue(IdTile)&&IdTile<=this.maxid/*&&!this.isFull()*/)this.arTileForAdd.push(IdTile);
};

this.pushTileCube = function (strXspaceZ) {                 
    if(true/*!this.tileCubeinQueue(strXspaceZ)*/){this.arTileCubeForAdd.push(strXspaceZ);}
};

this.needforload = function () {
   if(this.indx<this.arTileForAdd.length||this.indxCube<this.arTileCubeForAdd.length)return true;
   else{return false;}
}   

      //load and check flag of readiness
this.loadTile = function () {
   if(this.ready==true&&(this.indx<this.arTileForAdd.length||this.indxCube<this.arTileCubeForAdd.length)){
      if(this.indx<this.arTileForAdd.length&&this.requestBld){
         var id=this.arTileForAdd[this.indx];
         if(id>=0)
		 {
		    this.indx++;
			this.ready=false;
			//land_func(id);
			if(this.indxCube<this.arTileCubeForAdd.length)this.requestBld=false;
		 }
	  }
	else{
		if(this.indxCube<this.arTileCubeForAdd.length){
		    var id=this.arTileCubeForAdd[this.indxCube];
		    if(id.length>0){
			   this.indxCube++;this.ready=false;
			   var lanlot=id.split(' ');
			   build_func(lanlot[0],lanlot[1],lanlot[2],lanlot[3],lanlot[4]);
			   //var xz=id.split(' ');
			   //build_func(parseInt(xz[0]),parseInt(xz[1]));
			   this.requestBld=true;
			   }
			}
	}

	                               }						   
};

	//set flag of readiness
this.loaded = function () { 
    this.ready=true;
    //return this.arTileForAdd.pop();  //delete the tile from the queue
};

}

            function getTanDeg(deg) {

               var rad = deg * Math.PI/180;

               return Math.tan(rad)

            }

			function verdrop(id,x,z,lvl)
				{
				  var dist=getDistance(camera,lvl,x,z);	
				  var pixelTileSize=tileSizeRoot/ Math.pow(2,lvl)*UnitToPixelScale/dist;
				  //if(dist<=200&&lvlbldactive<0)lvlbldactive=lvl;

				  /*if(lvl==8)
			      {
				   var minlon=tile2lon(x,lvl)
				   var maxlon=tile2lon(x+1,lvl)
				   var minlat=tile2lat(z+1,lvl)
				   var maxlat=tile2lat(z,lvl)

				   }*/

			      if(lvl==lvlbldactive&&!arrTileBlds[id])
			      {
				   if(distfor17<0)distfor17=dist;
				   arrTileBlds[id]=new TileBlds();
				   arrTileBlds[id].id=id;
				   var minlon=tile2lon(x,lvl)
				   var maxlon=tile2lon(x+1,lvl)
				   var minlat=tile2lat(z+1,lvl)
				   var maxlat=tile2lat(z,lvl)
			       var range_lon=maxlon-minlon;
			       var range_lat=maxlat-minlat;
				   //alert(minlon+" "+minlat+" "+maxlon+" "+maxlat)
				   var c=new Array();
				   var var1=Math.pow(2,lvl);//number of tiles in row (specific lvl) 
				   var scale=id==0?TLoad.stepGrid:TLoad.stepGrid/(var1);//determine a width and a height of cell
				   var offset=id==0?0:Math.abs(2*TLoad.startX)/(var1);  // determine an offset for 1st tile of specific lvl 
				   var startX=TLoad.startX+offset*x;
				   var startZ=TLoad.startZ+offset*z;
				   var x_=-1;
				   var z_=-1;
				   var i_=0;
				   var j_=0;
				   //Creation of a grid
				   for(;i_<5;i_+=4){
				   	z_=startZ+(scale)*i_;
				   	for(;j_<5;j_+=4){
				   		x_=startX+(scale)*j_;
				           c.push(new THREE.Vector3(x_,0.0,z_));
						   //alert(x_+" "+z_+" "+startX+" "+scale)
				   				}
				   				j_=0;
				   				};
					/*for(var v=0;v<4;v++){
                       alert(c[v].x+" "+c[v].y+" "+c[v].z)
                     }	*/				

			       var range_x=Math.max(c[1].x,c[0].x)-Math.min(c[1].x,c[0].x);
			       var range_z=Math.max(c[0].z,c[2].z)-Math.min(c[0].z,c[2].z);
				   arrTileBlds[id].scale_x=range_x/range_lon;
			       arrTileBlds[id].scale_z=range_z/range_lat;
				   arrTileBlds[id].minlon=minlon;
				   arrTileBlds[id].minlat=minlat;
				   arrTileBlds[id].z=c[3].z;
				   arrTileBlds[id].x=c[0].x;

                   TLoad.pushTileCube(""+id+" "+minlon+" "+minlat+" "+maxlon+" "+maxlat);

			     }

				  if(lvl<18&&pixelTileSize>=384)
				  {

					/*timeoutId = setTimeout(*/verdrop(id*4+1,2*x,2*z,lvl+1)//, 5);
					/*timeoutId = setTimeout(*/verdrop(id*4+2,2*x+1,2*z,lvl+1)//, 5)
					/*timeoutId = setTimeout(*/verdrop(id*4+3,2*x,2*z+1,lvl+1)//, 5)
					/*timeoutId = setTimeout(*/verdrop(id*4+4,2*x+1,2*z+1,lvl+1)//, 5);

				  }
				  else{
				    var tileId=id;
					arrTile[tileId]=new Tile();
					arrTile[tileId].id=tileId;
					/*alert(tileId+" "+arrTile[tileId].id);*/
					arrTile[tileId].tex_x=x;
					arrTile[tileId].tex_z=z;
					arrTile[tileId].lvl=lvl;
					arrTile[tileId].prnt=tileId==0?-1:((tileId-1)-((tileId-1)%4))/4;
					initTiles[lvl].push(id);
					/*TLoad.pushTile(id);*//*arrCurRoot.push((id));*/ 
					return 0;
					}

				}

            function setMinMax(minlon,minlat,maxlon,maxlat,Camera,CameraController)
            {		
              var minlon = minlon;
              var minlat = minlat;
              var maxlon = maxlon;
              var maxlat = maxlat;

              var cenlon=(maxlon-minlon)/2 + minlon
              var cenlat=(maxlat-minlat)/2 + minlat
              var _x=lon2tile(cenlon,18)
              var _z=lat2tile(cenlat,18)
              var num18trow=Math.pow(2,18)-1
              var _k=tileSizeRoot/num18trow
              var coordx=_x*_k-20038706.7904
              var coordz=_z*_k-20038706.7904
              var tilesize=(lon2tile(maxlon,18)-lon2tile(minlon,18))*152.8832
			  var coordy=Math.max(500,tilesize*UnitToPixelScale/256); //256-na lvl nige512
              Camera.position.set(coordx, coordy, coordz);
              CameraController.target.x+=coordx
              CameraController.target.z+=coordz
            }

            function setPointZoom(cenlon,cenlat,zoom,Camera,CameraController)
            {	
              var cenlon=cenlon
              var cenlat=cenlat
              var zoom=zoom
              var _x=lon2tile(cenlon,18)
              var _z=lat2tile(cenlat,18)
              var lon1=tile2lon(_x,zoom);
              var lon2=tile2lon(_x+1,zoom);
              var numzoomtrow=Math.pow(2,18)-1
              var _k=tileSizeRoot/numzoomtrow
              var coordx=_x*_k-20038706.7904
              var coordz=_z*_k-20038706.7904
              var tilesize=(lon2tile(Math.max(lon2,lon1),zoom)-lon2tile(Math.min(lon2,lon1),zoom))*Math.pow(2,18-zoom)*152.8832
              var coordy=Math.max(500,tilesize*UnitToPixelScale/256)  ; //256-na lvl nige	
              Camera.position.set(coordx, coordy, coordz);
              CameraController.target.x+=coordx
              CameraController.target.z+=coordz
            }			

			function init() {
			    for(var i=0;i<19;i++)initTiles[i]=new Array();
			    //land_func(0);// load 1st tileroots
				//camera = new THREE.PerspectiveCamera( 45, window.innerWidth / window.innerHeight, 0.01, 10000000 );
				camera = new OSMEX.Camera( window.innerWidth,window.innerHeight,45, 1, 40000000 , 1, 20000000 );
				//camera.position.set(0, 3454245.2736, 0.0);
				UnitToPixelScale = window.innerHeight /( 2.0 * getTanDeg(camera.fov / 2.0));
                                cameraController = new OSMEX.CameraController( camera );
                                cameraController.maxPolarAngle = Math.PI / 2.1; // limit vertical rotation to prevent rotating under ground
				cameraController.ZoomSpeed = 0.43;


				if(typeof(landscapeMode) != "undefined" && landscapeMode != null)
				{
	               if(landscapeMode=='boundary')
	               {
	                 setMinMax(minlon,minlat,maxlon,maxlat,camera,cameraController)
	               }
	               else if(landscapeMode=='zoom')
	               {
	                 setPointZoom(mlon,mlat,zoom,camera,cameraController)
	               }
				   else if(landscapeMode=='camera')
				   {
                            /*var num18inRow=Math.pow(2,18)-1;
                            var _k=tileSizeRoot/num18inRow;
			  
                            var _x = (camx + 20038706.7904)/_k;
                            var _z = (camz + 20038706.7904)/_k;
			  
                            var cenlon = tile2lon(_x,18);
                            var cenlat = tile2lat(_z,18);
                            var range = (camy * 256)/UnitToPixelScale;
                            var num18inRange = range/_k;
                            var offset = parseInt(num18inRange/2);

                            var _minlon = tile2lon(_x-offset,18);
							var _maxlon = tile2lon(_x+offset,18);
							var offsetLat = (_maxlon - cenlon)/2;
                            var _minlat = cenlat - offsetLat;
                            var _maxlat = cenlat + offsetLat;
							
							if(_minlon<(-180))_minlon=-180;
							if(_minlon>(180))_minlon=180;
							
							if(_minlat<(-90))_minlat=-90;
							if(_minlat>(90))_minlat=90;
							
							if(_maxlon>180)_maxlon=180;
							if(_maxlon<(-180))_maxlon=-180;
							
							if(_maxlon>90)_maxlon=90;
							if(_maxlon<(-90))_maxlon=-90;
							
							parent.landscapeMode='boundary';
							parent.minlon = _minlon;
							parent.maxlon = _maxlon;
							parent.minlat = _minlat;
							parent.maxlat = _maxlat;
							setMinMax(_minlon,_minlat,_maxlon,_maxlat,camera,cameraController);*/
							camera.position.set(camx, camy, camz);
                            cameraController.target.x=tarx
                            cameraController.target.z=tarz
							
				   }
				}else
				{
				  setPointZoom(0,0,0,camera,cameraController)
				  //setPointZoom(10.86388,48.359621,17,camera,cameraController)
				  //setMinMax(25.64,44.4,39.95,54.18,camera,cameraController)
				}
                                
            var div = document.getElementById('cont');
			div.style.display="none";
			div.ongetdata =responseServer;		

			var div_bld = document.getElementById('build');
			div_bld.ongetdata =responseServerCubes;
			div_bld.style.display="none";

				//cameraController.rotateSpeed = 0.01;

				//cameraController.addEventListener( 'change', render/*checkTiles*/ );
                //timerid=setTimeout(verify, 25);

				//scene
                                scene = new THREE.Scene();
                                scene.fog = new THREE.Fog(0xccf2ff, 1, 40000000);
                                                //scene.fog = new THREE.FogExp2( 0xcccccc, 0.002 );

                                scene.add(new THREE.AmbientLight(0x3f3f3f));

                                objectLight = new THREE.DirectionalLight(0xffffff);
                                objectLight.target.position.copy(cameraController.target);
                                objectLight.position.copy(camera.position);
                                scene.add(objectLight);


				// renderer

				renderer = new THREE.WebGLRenderer( { antialias: false  } );//,preserveDrawingBuffer: true
				//renderer.setClearColor( scene.fog.color, 1 );
				//renderer.setDepthTest(true);
				//renderer.autoClear = true;
                                renderer.setClearColor(scene.fog.color, 1);
                                renderer.autoClear = false;
                
				renderer.setSize( window.innerWidth, window.innerHeight );

				container = document.getElementById( 'container' );
				container.appendChild( renderer.domElement );

				maxAnisotropy = renderer.getMaxAnisotropy();
                                
                                 function tile2long(x,z) {
                                  return (x/Math.pow(2,z)*360-180);
                                 }
                                 function tile2lat(y,z) {
                                  var n=Math.PI-2*Math.PI*y/Math.pow(2,z);
                                  return (180/Math.PI*Math.atan(0.5*(Math.exp(n)-Math.exp(-n))));
                                 }
                                
                                areaSelector = new OSMEX.AreaSelector( function(startPoint, endPoint)
                                {
                                    // TODO: should be reimplemented with more precise algorithm
                                    
                                    var halfSize = 20038706.7904;
                                    var tileSize = 152.8832;
                                    var tileLevel = 18;
                                    
                                    var x1 = (startPoint.x + halfSize) / tileSize;
                                    var y1 = (startPoint.z + halfSize) / tileSize;
                                    
                                    var x2 = (endPoint.x + halfSize) / tileSize;
                                    var y2 = (endPoint.z + halfSize) / tileSize;
                                    
                                    var lon1 = tile2long(x1, tileLevel);
									lon1 = lon1.toFixed(6);
                                    var lon2 = tile2long(x2, tileLevel);
									lon2 = lon2.toFixed(6);
                                    
                                    var lat1 = tile2lat(y1, tileLevel);
									lat1 = lat1.toFixed(6);
                                    var lat2 = tile2lat(y2, tileLevel);
									lat2 = lat2.toFixed(6);
                                    
                                    parent.EDIT_MIN_LON = Math.min(lon1, lon2);  // LEFT
                                    parent.EDIT_MIN_LAT = Math.min(lat1, lat2);  // BOTTOM
                                    
                                    parent.EDIT_MAX_LON = Math.max(lon1, lon2);  // RIGHT
                                    parent.EDIT_MAX_LAT = Math.max(lat1, lat2);  // TOP
                                    //alert(parent.EDIT_MIN_LON+" "+parent.EDIT_MIN_LAT+" "+parent.EDIT_MAX_LON +" "+parent.EDIT_MAX_LAT);	
                                    //alert(camera.position.x+" "+camera.position.y+" "+camera.position.z)
									parent.enableMapEditing();
                                    
                                });
                                scene.add(areaSelector);

				//
                                //On Window Resize
                                
				window.addEventListener( 'resize', onWindowResize, false );
               //wrt("clear")
			  /* for(var i = 0; i < tiles[0].triangleGeometry.vertices.length; i++) {
                         wrt(""+i+" "+tiles[0].triangleGeometry.vertices[i].x+" "+tiles[0].triangleGeometry.vertices[i].y+" "+tiles[0].triangleGeometry.vertices[i].z)
	                  }
					  wrt("center "+tiles[0].center.x+" "+tiles[0].center.y+" "+tiles[0].center.z)*/




			   /*onkeypress = function (event) {
	               if ((event = event || window.event).keyCode == 37)camera.center.x-=0.25
		           if ((event = event || window.event).keyCode == 39)camera.center.x+=0.25
				   if ((event = event || window.event).keyCode == 38)camera.center.z-=0.25
				   if ((event = event || window.event).keyCode == 40)camera.center.z+=0.25
	           }*/

			   //land_func(300)

			  verdrop(0,0,0,0);
			  /*initTiles = initTiles.concat(initTiles[0],initTiles[1],initTiles[2],initTiles[3],initTiles[4],initTiles[5],
			  initTiles[6],initTiles[7],initTiles[8],initTiles[9],initTiles[10],initTiles[11],initTiles[12],initTiles[13],
			  initTiles[14],initTiles[15],initTiles[16],initTiles[17],initTiles[18])*/
			  
			  //for (var beg=initTiles.length -1;beg>=0;beg--)TLoad.pushTile(initTiles[beg]);//TLoad.pushTile(initTiles[beg]);
			  //document.addEventListener('keydown',onDocumentKeyDown,false);
			  
			  //render();
			  sketchFactory = new OSMEX.SketchFactory();
              buildingsMaterial = new THREE.MeshLambertMaterial( { color: 0xeeeeee, shading: THREE.FlatShading, transparent: true } );
			  //timer=setInterval( checkTiles , 15);
			  
			  timerid=setTimeout(checkTiles, 20);

			}



			function initFaceTex(tile) {
					//Faces
                	for(ix=0;ix<4;ix++){//collumn
                	   for(iy=0;iy<4;iy++){//row of quads
                	       tile.triangleGeometry.faces.push(new THREE.Face3(5*ix+iy+5,5*ix+iy+1,5*ix+iy));
                	       tile.triangleGeometry.faces.push(new THREE.Face3(5*ix+iy+5,5*ix+iy+6,5*ix+iy+1));
	                	                }
                					}

                    //UV
                	step=1.0/4.0
                	for(v=1.0;v>0;v-=step){
                	  for(u=0.0;u<1;u+=step){
                	tile.triangleGeometry.faceVertexUvs[0].push( [
                            new THREE.Vector2(u, v-step) ,
                            new THREE.Vector2(u+step, v) ,
                			new THREE.Vector2(u, v)
                        ] );
                	tile.triangleGeometry.faceVertexUvs[0].push( [
                            new THREE.Vector2(u, v-step) ,
                            new THREE.Vector2(u+step, v-step) ,
                            new THREE.Vector2(u+step, v )
                        ] );
                		                      }
	                	                   }			

			}


			//function is called in response to a request from the server to get the tile by id
			function responseServer(s) {

				var tileId=-1;
				jstr=JSON.parse(''+s);
				if(jstr.id>0&&typeof(arrTile[jstr.id]) != "undefined" && arrTile[jstr.id] != null){
				var id =jstr.id;
				var var1=Math.pow(2,arrTile[id].lvl);//number of tiles in row (specific lvl) 
				scale=id==0?TLoad.stepGrid:TLoad.stepGrid/(var1);//determine a width and a height of cell

				var offset=id==0?0:Math.abs(2*TLoad.startX)/(var1);  // determine an offset for 1st tile of specific lvl 
				//count 1st coordinates for concrete tile
				var startX=TLoad.startX+offset*arrTile[id].tex_x;
				var startZ=TLoad.startZ+offset*arrTile[id].tex_z;
				
				arrTile[id].triangleGeometry.dynamic = true;

				var x_=-1;
				var z_=-1;
				var i_=0;
				var j_=0;
				//Creation of a grid
                    for(;i_<5;i_++){
					    z_=startZ+(scale)*i_;
					   for(;j_<5;j_++){
					      x_=startX+(scale)*j_;
		                  arrTile[id].triangleGeometry.vertices[i_*5+j_].y=parseFloat(jstr.verts[i_*5+j_]);
                          //console.debug(parseFloat(jstr.verts[i_*5+j_]));

						             }
									 j_=0;
											};
											
				arrTile[id].triangleGeometry.verticesNeedUpdate = true;
				
				}
				

                jstr=null;					
				                      }
									  

			function responseServerCubes(s) {

				var jstr;
				jstr=jQuery.parseJSON(s);
				if(typeof(arrTileBlds[jstr.tile_id]) != "undefined" && arrTileBlds[jstr.tile_id] != null)
				{
				  if(jstr.tile_id>=0&&arrTileBlds[jstr.tile_id].id!=undefined)
				  {
				
				   var id=jstr.tile_id;
				   //alert(id)
				   //alert("builtile "+id)
                   for(var j=0;j<jstr.builds.length;j++)
				   {
				       var b=parseInt(jstr.builds[j].id);
                                       
                                       
                                       
                       var loadBuildingFunc = function(building) { return function(obj) {

                            obj.id = id;
                            obj.scale = new THREE.Vector3 (parseFloat(building.scaleX)*1.5, parseFloat(building.scaleY),parseFloat(building.scaleZ)*1.5);
                            obj.rotation = new THREE.Vector3 (parseFloat(building.rotationX), parseFloat(building.rotationY),parseFloat(building.rotationZ));
                            var lon=parseFloat(building.positionLon);///OSM_w;
                            var lat=parseFloat(building.positionLat);///OSM_h;
			    obj.position.set(arrTileBlds[id].x+(lon-arrTileBlds[id].minlon)*arrTileBlds[id].scale_x,building.positionHeight,arrTileBlds[id].z-(lat-arrTileBlds[id].minlat)*arrTileBlds[id].scale_z);
			    obj.TypeID = building.TypeID;
                            obj.material = buildingsMaterial;
			    MeshOfBlds[b]=obj;
			    scene.add(MeshOfBlds[b]);
                            arrTileBlds[id].arrIndxsBlds[j]=b;   
                            
                        } }(jstr.builds[j]);

                        sketchFactory.createObject(jstr.builds[j].TypeID, loadBuildingFunc);        
						
					}
				//render();
				//arrCurBld.push(id);	
                }				
				}
				jstr=null;
				TLoad.loaded()	
               }				


			function onWindowResize() {

				/*camera.aspect = window.innerWidth / window.innerHeight;
				camera.updateProjectionMatrix();*/

				UnitToPixelScale = window.innerHeight /( 2.0 * getTanDeg(camera.fov / 2.0));

				camera.setSize(window.innerWidth, window.innerHeight);
                camera.updateProjectionMatrix();

				renderer.setSize( window.innerWidth, window.innerHeight );

			}

			/*function verify(){

                TLoad.loadTile();
				
			    //timerid=setInterval(verify, 20);
			}*/

			function getDistance(cam,tlvl,tosmX,tosmZ){


				var var1=Math.pow(2,tlvl);//number of tiles in row (specific lvl) 
				var scale=tlvl==0?TLoad.stepGrid:TLoad.stepGrid/(var1);//determine a width and a height of cell
				var offset=tlvl==0?0:Math.abs(2*TLoad.startX)/(var1);  // determine an offset for 1st tile of specific lvl 

				var vec1X=TLoad.startX+offset*tosmX;
				var vec1Z=TLoad.startZ+offset*tosmZ;

				var vec2X=TLoad.startX+offset*tosmX+(scale)*4;
				var vec2Z=TLoad.startZ+offset*tosmZ;

				var vec3X=TLoad.startX+offset*tosmX;
				var vec3Z=TLoad.startZ+offset*tosmZ+(scale)*4;

				var vec4X=TLoad.startX+offset*tosmX+(scale)*4;
				var vec4Z=TLoad.startZ+offset*tosmZ+(scale)*4;

                var cenx=(vec2X+vec1X)/2.0;
                var cenz=(vec2Z+vec3Z)/2.0;

				var tilecenter=new THREE.Vector3( cenx, 0.0, cenz);
				/*tex
				var ax=Math.max(cam.position.x,cenx)-Math.min(cam.position.x,cenx);
				var ay=Math.max(cam.position.y,0)-Math.min(cam.position.y,0);
				var az=Math.max(cam.position.z,cenz)-Math.min(cam.position.z,cenz);
				var cD=Math.sqrt(ax*ax+ay*ay+az*az);*/
				//cD=1 * cD.toFixed(1)

                return tilecenter.sub(cam.position).length();				
			}

			function deltilemesh(id,req){

				if(req==false)req=false;
				else{req=true;}
				if(typeof(triangleMesh[id]) != "undefined" && triangleMesh[id] != null)
				{
			      scene.remove(triangleMesh[id]);
			      /*triangleMesh[id].geometry.deallocate();
			      triangleMesh[id].material.deallocate();
			      triangleMesh[id].deallocate();*/

			      //renderer.deallocateObject(triangleMesh[id]);
			      triangleMesh[id].geometry.dispose();
			      triangleMesh[id].material.dispose();
			      //renderer.deallocateTexture(arrTex[id]);
				  if(typeof(arrTex[id]) != "undefined" && arrTex[id] != null)
				  {
			        arrTex[id].dispose()
			        delete arrTex[id];
			        arrTex[id]=null;
				  }
			      r=delete triangleMesh[id];
			      triangleMesh[id]=null

				}
				if(req){
				  if(triangleMesh[(id*4+1)])deltilemesh((id*4+1));
				  if(triangleMesh[(id*4+2)])deltilemesh((id*4+2));
				  if(triangleMesh[(id*4+3)])deltilemesh((id*4+3));
				  if(triangleMesh[(id*4+4)])deltilemesh((id*4+4));
				}
			}

            function deltile(id,req){
                if(req==false)req=false;
				else{req=true;}
				if(typeof(arrTile[id]) != "undefined" && arrTile[id] != null)
				{
                  var dist=getDistance(camera,arrTile[id].lvl,arrTile[id].tex_x,arrTile[id].tex_z);
                  if(arrTileBlds[id]&&dist>=/*210*/distfor17+10)delbuildsoftile(id);
                  arrTile[id].destroy();
                  delete arrTile[id];
                  arrTile[id]=null;

				}
				if(req){
				  if(arrTile[(id*4+1)])deltile((id*4+1));
				  if(arrTile[(id*4+2)])deltile((id*4+2));
				  if(arrTile[(id*4+3)])deltile((id*4+3));
				  if(arrTile[(id*4+4)])deltile((id*4+4));
				}

			}

			function delbuildsoftile(id){
			    if(arrTileBlds[id]){
				  //alert("del "+id+" "+id)
				  if(arrTileBlds[id].arrIndxsBlds[0]!=undefined)
				    {
					//alert("del arrTileBlds[id].arrIndxsBlds.length() "+arrTileBlds[id].arrIndxsBlds.length())
				     for(var i in arrTileBlds[id].arrIndxsBlds)
		                {
						  var b=arrTileBlds[id].arrIndxsBlds[i];
		                  //alert("del build "+b)
		                  scene.remove(MeshOfBlds[b]);
                          //renderer.deallocateObject(MeshOfBlds[b]);
						  MeshOfBlds[b].geometry.dispose();
						  //MeshOfBlds[b].material.dispose();
			              //renderer.deallocateTexture(arrTex[id]);
			              //delete arrTex[id];
			              //arrTex[id]=null;
			              delete MeshOfBlds[b];
			              MeshOfBlds[b]=null
		                }
		              //arrTileBlds[id].arrIndxsBlds.splice(0,arrTileBlds[id].arrIndxsBlds.length);
		             }
				  arrTileBlds[id].destroy();
				  delete arrTileBlds[id];
				  arrTileBlds[id]=null;
				 }


			}
			

			function crtMesh(id,flagroot){

				var var1=Math.pow(2,arrTile[id].lvl);//number of tiles in row (specific lvl) 
				scale=id==0?TLoad.stepGrid:TLoad.stepGrid/(var1);//determine a width and a height of cell

				var offset=id==0?0:Math.abs(2*TLoad.startX)/(var1);  // determine an offset for 1st tile of specific lvl 
				//count 1st coordinates for concrete tile
				var startX=TLoad.startX+offset*arrTile[id].tex_x;
				var startZ=TLoad.startZ+offset*arrTile[id].tex_z;

				var x_=-1;
				var z_=-1;
				var index_=0;
				var i_=0;
				var j_=0;
				//Creation of a grid
                    for(;i_<5;i_++){
					    z_=startZ+(scale)*i_;
					   for(;j_<5;j_++){
					      x_=startX+(scale)*j_;
		                  arrTile[id].triangleGeometry.vertices.push(new THREE.Vector3( x_,0.0,z_));

						  index_++;
						             }
									 j_=0;
											};
				initFaceTex(arrTile[id]);


				/*var tex=''+arrTile[id].lvl+'/'+arrTile[id].tex_x+'/'+arrTile[id].tex_z;
				arrTex[id]=THREE.ImageUtils.loadTexture('http://c.tile.openstreetmap.org/'+tex+".png",new THREE.UVMapping(),function()
				  {
				     arrTile[id].texExist=true;
					 if(typeof(flagroot) != "undefined" && flagroot != null){
                         


                    deltilemesh((id*4+1));
					deltilemesh((id*4+2));
					deltilemesh((id*4+3));
					deltilemesh((id*4+4));
					deltile((id*4+1));
					deltile((id*4+2));
					deltile((id*4+3));
					deltile((id*4+4));
					}
					else{
                     var delprntid=id==0?-1:((id-1)-((id-1)%4))/4;

					 deltilemesh(delprntid);
					 deltile(delprntid);
					 }
					 
                     //render();
	            });*/

                arrTex[id].magFilter = THREE.LinearFilter;
                arrTex[id].minFilter = THREE.LinearFilter;
				arrTex[id].anisotropy = maxAnisotropy;
				var triangleMaterial = new THREE.MeshBasicMaterial({
				//'map':texture,
				'map': arrTex[id],
				//wireframe: true,
				//side:THREE.DoubleSide//,
                //'overdraw': false
				                });				

                triangleMesh[id] = new THREE.Mesh(arrTile[id].triangleGeometry, triangleMaterial);
				triangleMesh[id].position.set(0.0, 0.0, 0.0);
				scene.add(triangleMesh[id]);
				triangleMesh[id].visible=true;
				//if(arrTile[id].lvl>15)triangleMesh[id].visible=false;


				
				//arrCurRoot.push(id);
				
				//render();
				land_func(id);

			}
			var serverCount = 0;
			function loadTexture(id){
                            
                            var chr = String.fromCharCode(97 + serverCount); // fetching a, b, c
                            if (++serverCount > 2) serverCount = 0;
			
			    var tex=''+arrTile[id].lvl+'/'+arrTile[id].tex_x+'/'+arrTile[id].tex_z;
				arrTex[id]=THREE.ImageUtils.loadTexture('http://' + chr + '.tile.openstreetmap.org/'+tex+".png",new THREE.UVMapping(),function()
				  {
				     arrTile[id].texExist=true;
				  });

			}
			
			function dist2Blds(tid,cam){
                var cenx=arrTileBlds[tid].cenx;
                var cenz=arrTileBlds[tid].cenz;
				var tilecenter=new THREE.Vector3( cenx, 0.0, cenz);
                return tilecenter.sub(cam.position).length();
            }				
			
			function loadBlds(tid,cam,tlvl,tosmX,tosmZ){

				var var1=Math.pow(2,tlvl);//number of tiles in row (specific lvl) 
				var scale=tlvl==0?TLoad.stepGrid:TLoad.stepGrid/(var1);//determine a width and a height of cell
				var offset=tlvl==0?0:Math.abs(2*TLoad.startX)/(var1);  // determine an offset for 1st tile of specific lvl 

				var vec1X=TLoad.startX+offset*tosmX;
				var vec1Z=TLoad.startZ+offset*tosmZ;

				var vec2X=TLoad.startX+offset*tosmX+(scale)*4;
				var vec2Z=TLoad.startZ+offset*tosmZ;

				var vec3X=TLoad.startX+offset*tosmX;
				var vec3Z=TLoad.startZ+offset*tosmZ+(scale)*4;

				var vec4X=TLoad.startX+offset*tosmX+(scale)*4;
				var vec4Z=TLoad.startZ+offset*tosmZ+(scale)*4;

                var cenx=(vec2X+vec1X)/2.0;
                var cenz=(vec2Z+vec3Z)/2.0;

				var tilecenter=new THREE.Vector3( cenx, 0.0, cenz);

                var dist = tilecenter.sub(cam.position).length();

                if(dist<=100)
                {
                  if(tlvl<18)
				  {
				    loadBlds(tid*4+1,cam,tlvl+1,2*tosmX,2*tosmZ);
					loadBlds(tid*4+2,cam,tlvl+1,2*tosmX+1,2*tosmZ);
					loadBlds(tid*4+3,cam,tlvl+1,2*tosmX,2*tosmZ+1);
					loadBlds(tid*4+4,cam,tlvl+1,2*tosmX+1,2*tosmZ+1);
				  }
				  if(tlvl==18 && (typeof(arrTileBlds[tid]) == "undefined" || arrTileBlds[tid] == null))
			      {
				    arrTileBlds[tid]=new TileBlds();
				    arrTileBlds[tid].id=tid;
				    var minlon=tile2lon(tosmX,tlvl)
				    var maxlon=tile2lon(tosmX+1,tlvl)
				    var minlat=tile2lat(tosmZ+1,tlvl)
				    var maxlat=tile2lat(tosmZ,tlvl)
				    var range_lon=maxlon-minlon;
				    var range_lat=maxlat-minlat;
				    var c0=new THREE.Vector3( vec1X,0.0,vec1Z);
				    var c1=new THREE.Vector3( vec2X,0.0,vec2Z);
				    var c2=new THREE.Vector3( vec3X,0.0,vec3Z);
				    var c3=new THREE.Vector3( vec4X,0.0,vec4Z);
				    var range_x=Math.max(c1.x,c0.x)-Math.min(c1.x,c0.x);
				    var range_z=Math.max(c0.z,c2.z)-Math.min(c0.z,c2.z);
				    arrTileBlds[tid].scale_x=range_x/range_lon;
				    arrTileBlds[tid].scale_z=range_z/range_lat;
				    arrTileBlds[tid].minlon=minlon;
				    arrTileBlds[tid].minlat=minlat;
				    arrTileBlds[tid].z=c3.z;
				    arrTileBlds[tid].x=c0.x;
					arrTileBlds[tid].cenx=cenx;
				    arrTileBlds[tid].cenz=cenz;
					//alert(8)
				    TLoad.pushTileCube(""+tid+" "+minlon+" "+minlat+" "+maxlon+" "+maxlat);
					//build_func(tid,minlon,minlat,maxlon,maxlat)
					/*if(TLoad&&!bverify){alert("bv");
			            if(TLoad.needforload()){alert(tid);bverify=true;timerid=setTimeout(verify, 25);}
				      }*/
			      }
                }				
			}

			function checkTiles() {

				
				
			if(initTileslvl>=0)
			  {
			   while(1)
			   {
			    if(initTiles[initTileslvl].length>0)break;
			    else{initTileslvl--;if(initTileslvl<0)break;}
			   }
			   if(initTileslvl>=0&&initTilesIndx<initTiles[initTileslvl].length)
			   {
			    var tex=''+arrTile[initTiles[initTileslvl][initTilesIndx]].lvl+'/'+arrTile[initTiles[initTileslvl][initTilesIndx]].tex_x+'/'+arrTile[initTiles[initTileslvl][initTilesIndx]].tex_z;
				if(initReady)
				{
			    initReady=false;
                            
                            var chr = String.fromCharCode(97 + serverCount); // fetching a, b, c
                            if (++serverCount > 2) serverCount = 0;
                            
				arrTex[initTiles[initTileslvl][initTilesIndx]]=THREE.ImageUtils.loadTexture('http://' + chr + '.tile.openstreetmap.org/'+tex+".png",new THREE.UVMapping(),function()
				  {
				     //alert("o "+initTiles[initTilesIndx]);
				     arrTile[initTiles[initTileslvl][initTilesIndx]].texExist=true;
					 crtMesh(initTiles[initTileslvl][initTilesIndx]);
					 arrCurRoot.push(initTiles[initTileslvl][initTilesIndx]);
					 initTilesIndx++;
					 if(initTilesIndx>=initTiles[initTileslvl].length)
					 {
					 initTilesIndx=0;
					 initTileslvl--;
					 }
					 initReady=true;
				  });
                };
               }				
			 }
			//else{
                   //console.debug("arrCurRoot.length "+arrCurRoot.length);
			/*	

			  curBldId++;
			  if(curBldId>=arrCurBld.length)curBldId=0;
			  var curbld=arrCurBld[curBldId];
			  if(arrTileBlds[curbld])
			  if(typeof(arrTileBlds[curbld]) != "undefined" && arrTileBlds[curbld] != null)
			  {
			  var dist2b=dist2Blds(curbld,camera);

			  alert(arrTileBlds[curbld].id)
			  if(dist2b>110){arrCurBld.splice(curBldId,1);delbuildsoftile(curbld);}
			  }*/


				//&&TLoad.idforloadroot!=arrCurRoot[j]&&TLoad.ReadyForRoot
				var InitArray = new Array();
				
				for(j=0;j<arrCurRoot.length;j++){ 
				  cur_ID=arrCurRoot[j];
				  //console.debug("cur_ID "+cur_ID)
				  if(typeof(arrTile[cur_ID]) != "undefined" && arrTile[cur_ID] != null)
				  {

				    flagDrop=false;
			        chldsExist=true;

			  var dist=getDistance(camera,arrTile[cur_ID].lvl,arrTile[cur_ID].tex_x,arrTile[cur_ID].tex_z);	
			  var pixelTileSize=tileSizeRoot/ Math.pow(2,arrTile[cur_ID].lvl)*UnitToPixelScale/dist;
			  
			  /*if(dist<=100)
			  {
			    loadBlds(cur_ID,camera,arrTile[cur_ID].lvl,arrTile[cur_ID].tex_x,arrTile[cur_ID].tex_z)
			  }*/
			  
              //if(dist<=200&&lvlbldactive<0)lvlbldactive=arrTile[cur_ID].lvl;
			  if(arrTile[cur_ID].lvl==lvlbldactive)
			  {
			    if(distfor17<0)distfor17=dist;
                if(typeof(arrTileBlds[cur_ID]) == "undefined" || arrTileBlds[cur_ID] == null)
				{
				 arrTileBlds[cur_ID]=new TileBlds();
				 arrTileBlds[cur_ID].id=cur_ID;
				 var minlon=tile2lon(arrTile[cur_ID].tex_x,arrTile[cur_ID].lvl)
				 var maxlon=tile2lon(arrTile[cur_ID].tex_x+1,arrTile[cur_ID].lvl)
				 var minlat=tile2lat(arrTile[cur_ID].tex_z+1,arrTile[cur_ID].lvl)
				 var maxlat=tile2lat(arrTile[cur_ID].tex_z,arrTile[cur_ID].lvl)
			     var range_lon=maxlon-minlon;
			     var range_lat=maxlat-minlat;
			     var c0=triangleMesh[cur_ID].geometry.vertices[0];
			     var c1=triangleMesh[cur_ID].geometry.vertices[4];
			     var c2=triangleMesh[cur_ID].geometry.vertices[20];
			     var c3=triangleMesh[cur_ID].geometry.vertices[24];
			     var range_x=Math.max(c1.x,c0.x)-Math.min(c1.x,c0.x);
			     var range_z=Math.max(c0.z,c2.z)-Math.min(c0.z,c2.z);
				 arrTileBlds[cur_ID].scale_x=range_x/range_lon;
			     arrTileBlds[cur_ID].scale_z=range_z/range_lat;
				 arrTileBlds[cur_ID].minlon=minlon;
				 arrTileBlds[cur_ID].minlat=minlat;
				 arrTileBlds[cur_ID].z=c3.z;
				 arrTileBlds[cur_ID].x=c0.x;
                 //alert(""+arrTile[cur_ID].id+" "+minlon+" "+minlat+" "+maxlon+" "+maxlat)
                 TLoad.pushTileCube(""+arrTile[cur_ID].id+" "+minlon+" "+minlat+" "+maxlon+" "+maxlat);
				}

			  }

			    if(pixelTileSize>=384&&arrTile[cur_ID].lvl<18)
				{

				  var ch1=cur_ID*4+1;
				  var ch2=cur_ID*4+2;
				  var ch3=cur_ID*4+3;
				  var ch4=cur_ID*4+4;
				  var flg=true;
				  if(typeof(arrTile[ch1]) == "undefined" || arrTile[ch1] == null)
				  {
				    flg=false;
					arrTile[ch1]=new Tile();arrTile[ch1].id=ch1;arrTile[ch1].tex_x=2*arrTile[cur_ID].tex_x;arrTile[ch1].tex_z=2*arrTile[cur_ID].tex_z;arrTile[ch1].lvl=arrTile[cur_ID].lvl+1;arrTile[ch1].prnt=cur_ID;
					loadTexture(ch1);
				  }
				  else{if(!arrTile[ch1].texExist)flg=false;}
				  
				  if(typeof(arrTile[ch2]) == "undefined" || arrTile[ch2] == null)
				  {
				    flg=false;
					arrTile[ch2]=new Tile();arrTile[ch2].id=ch2;arrTile[ch2].tex_x=2*arrTile[cur_ID].tex_x+1;arrTile[ch2].tex_z=2*arrTile[cur_ID].tex_z;arrTile[ch2].lvl=arrTile[cur_ID].lvl+1;arrTile[ch2].prnt=cur_ID;
					loadTexture(ch2);
				  }
				  else{if(!arrTile[ch2].texExist)flg=false;}
				  
				  if(typeof(arrTile[ch3]) == "undefined" || arrTile[ch3] == null)
				  {
				    flg=false;
					arrTile[ch3]=new Tile();arrTile[ch3].id=ch3;arrTile[ch3].tex_x=2*arrTile[cur_ID].tex_x;arrTile[ch3].tex_z=2*arrTile[cur_ID].tex_z+1;arrTile[ch3].lvl=arrTile[cur_ID].lvl+1;arrTile[ch3].prnt=cur_ID;
				    loadTexture(ch3);
				  }
				  else{if(!arrTile[ch3].texExist)flg=false;}
				  
				  if(typeof(arrTile[ch4]) == "undefined" || arrTile[ch4] == null)
				  {
				    flg=false;
					arrTile[ch4]=new Tile();arrTile[ch4].id=ch4;arrTile[ch4].tex_x=2*arrTile[cur_ID].tex_x+1;arrTile[ch4].tex_z=2*arrTile[cur_ID].tex_z+1;arrTile[ch4].lvl=arrTile[cur_ID].lvl+1;arrTile[ch4].prnt=cur_ID;
					loadTexture(ch4);
				  }
				  else{if(!arrTile[ch4].texExist)flg=false;}
				  
				  if(flg)
					{
				       arrCurRoot.splice(j,1);
				       var delprntid=ch1==0?-1:((ch1-1)-((ch1-1)%4))/4;
				       deltilemesh(delprntid,false);
				       deltile(delprntid,false);
				       crtMesh(ch1);
				       crtMesh(ch2);
				       crtMesh(ch3);
				       crtMesh(ch4);
					   arrCurRoot.push(ch1);
					   arrCurRoot.push(ch2);
					   arrCurRoot.push(ch3);
					   arrCurRoot.push(ch4);
				    }
				
				break;
				}
                  else{
				 //does tile have a parent
				 if(arrTile[cur_ID].prnt>=0){

				    prntId=(1*arrTile[cur_ID].prnt);
					ch_id1=4*prntId+1;
					ch_id2=4*prntId+2;
					ch_id3=4*prntId+3;
					ch_id4=4*prntId+4;
					allchexist=true;
					if(typeof(arrTile[ch_id1]) == "undefined" || arrTile[ch_id1] == null)allchexist=false;
					else{if(!arrTile[ch_id1].texExist)allchexist=false;}
					if(typeof(arrTile[ch_id2]) == "undefined" || arrTile[ch_id2] == null)allchexist=false;
					else{if(!arrTile[ch_id2].texExist)allchexist=false;}
					if(typeof(arrTile[ch_id3]) == "undefined" || arrTile[ch_id3] == null)allchexist=false;
					else{if(!arrTile[ch_id3].texExist)allchexist=false;}
					if(typeof(arrTile[ch_id4]) == "undefined" || arrTile[ch_id4] == null)allchexist=false;
					else{if(!arrTile[ch_id4].texExist)allchexist=false;}
					if(allchexist){				

					var distFromCh1=getDistance(camera,arrTile[ch_id1].lvl,arrTile[ch_id1].tex_x,arrTile[ch_id1].tex_z);
					var pixelTileSize1=tileSizeRoot/ Math.pow(2,arrTile[ch_id1].lvl)*UnitToPixelScale/distFromCh1;
				    var distFromCh2=getDistance(camera,arrTile[ch_id2].lvl,arrTile[ch_id2].tex_x,arrTile[ch_id2].tex_z);
					var pixelTileSize2=tileSizeRoot/ Math.pow(2,arrTile[ch_id2].lvl)*UnitToPixelScale/distFromCh2;
				    var distFromCh3=getDistance(camera,arrTile[ch_id3].lvl,arrTile[ch_id3].tex_x,arrTile[ch_id3].tex_z);
					var pixelTileSize3=tileSizeRoot/ Math.pow(2,arrTile[ch_id3].lvl)*UnitToPixelScale/distFromCh3;
				    var distFromCh4=getDistance(camera,arrTile[ch_id4].lvl,arrTile[ch_id4].tex_x,arrTile[ch_id4].tex_z);
					var pixelTileSize4=tileSizeRoot/ Math.pow(2,arrTile[ch_id4].lvl)*UnitToPixelScale/distFromCh4;


				 	if(pixelTileSize1<=128&&pixelTileSize2<=128&&pixelTileSize3<=128&&pixelTileSize4<=128)
					{

					  if(typeof(arrTile[prntId]) != "undefined" && arrTile[prntId] != null)
					  {
					    if(arrTile[prntId].texExist)
						{

						  for(var d=0;d<4;d++)
							for(var i=0 ;i< arrCurRoot.length;i++)
							{
							    if(typeof(arrTile[arrCurRoot[i]]) != "undefined" && arrTile[arrCurRoot[i]] != null)
								{
								
					              if(arrTile[arrCurRoot[i]].prnt==prntId){arrCurRoot.splice(i,1);break;}
								}
					        }

						   deltilemesh((prntId*4+1));
						   deltilemesh((prntId*4+2));
						   deltilemesh((prntId*4+3));
						   deltilemesh((prntId*4+4));
						   deltile((prntId*4+1));
						   deltile((prntId*4+2));
						   deltile((prntId*4+3));
						   deltile((prntId*4+4));
						   crtMesh(prntId);
						   arrCurRoot.push(prntId);
			  
						   
						   break;
						}
					  }
					  else
					  {
					  arrTile[prntId]=new Tile();
					  arrTile[prntId].id=prntId;
					  arrTile[prntId].tex_x=arrTile[(prntId*4+1)].tex_x/2;
				      arrTile[prntId].tex_z=arrTile[(prntId*4+1)].tex_z/2;
					  arrTile[prntId].lvl=arrTile[prntId*4+1].lvl-1;
					  arrTile[prntId].prnt=prntId==0?-1:((prntId-1)-((prntId-1)%4))/4;
					  loadTexture(prntId);
					  }

					  //if(!TLoad.tileinQueue(prntId))InitArray.push(prntId);

                    

					}

				    }}}		

                        }
						
						//}
						
						
				if(InitArray.length)		
				{		
                InitArray.sort();
				InitArray.reverse();
				TLoad.arTileForAdd = InitArray.concat(TLoad.arTileForAdd);
				TLoad.indx=0;
				}
				//if(TLoad)TLoad.loadTile();
                
				//render();

				/*if(TLoad&&!bverify){
			     if(TLoad.needforload()){bverify=true;timerid=setTimeout(verify, 35);}
				}*/
			  }
				if(TLoad){
			     if(TLoad.needforload())TLoad.loadTile();
				}
				
				timerid=setTimeout(checkTiles, 20);
              
			}

			function verify(){

				/*if(TLoad){
			     if(TLoad.needforload()){TLoad.loadTile();timerid=setTimeout(verify, 25);}
				 else{bverify=false;}
				}*/
			}
                        
                        function update() {

                            cameraController.update();
                            
                            var newFar = Math.max(camera.position.y * 10, 2500);

                            if (camera.cameraP.far != newFar) {

                                camera.cameraP.far = newFar;
                                camera.toPerspective();
                                
                                scene.fog.far = newFar;
                            }
                            
                            if (camera.position.y < 2000) {
                                $("#edit_button").removeClass("disabled");
                            } else {
                                $("#edit_button").addClass("disabled");
                            }
                            
                            /// TODO: HERE POS of CAMERA:
							parent.landscapeMode='camera';
							parent.camx=camera.position.x;
							parent.camy=camera.position.y;
							parent.camz=camera.position.z;
							
							parent.tarx=cameraController.target.x;
							parent.tarz=cameraController.target.z;
							
							objectLight.target.position.copy(cameraController.target);
                            objectLight.position.copy(camera.position);
			  
                        }

			function render() {

                            renderer.clear();
                            renderer.render( scene, camera);
			}
                        
                        function animate() {

                            requestAnimationFrame( animate );

                            update();
                            render();
			}
                        
                       function onDocumentMouseDown(event) {

                            event.preventDefault();

                            if (event.button == 0) {

                                areaSelector.onLeftMouseButtonDown(mouse);

                            }
                        }

                        function onDocumentMouseUp(event) {

                            event.preventDefault();

                            if (event.button == 0) {

                                areaSelector.onLeftMouseButtonUp(mouse);

                            }
                        }

                        function onDocumentMouseMove(event) {

                            event.preventDefault();

                            mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
                            mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

                            areaSelector.onMouseMove(mouse);
                        }



			function lon2tile(lon,zoom) {
			     return (Math.floor((lon+180)/360*Math.pow(2,zoom)));
			 }
            function lat2tile(lat,zoom)  { 
			    return (Math.floor((1-Math.log(Math.tan(lat*Math.PI/180) + 1/Math.cos(lat*Math.PI/180))/Math.PI)/2 *Math.pow(2,zoom)));
			}

			function tile2lon(x,z) {
			    return (x/Math.pow(2,z)*360-180);
 			}
 			function tile2lat(y,z) {
 			    var n=Math.PI-2*Math.PI*y/Math.pow(2,z);
 			    return (180/Math.PI*Math.atan(0.5*(Math.exp(n)-Math.exp(-n))));
 			}



		</script>

	</body>
</html>
8
