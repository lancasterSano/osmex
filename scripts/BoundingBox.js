var OSMEX = OSMEX || { REVISION: '1' };


OSMEX.BoundingBox = function ( target ) {
    
    THREE.Object3D.call( this );
    
    this.name = "BoundingBox";
    
    this.target = target;
    this.pickable = false;
   
    target.geometry.computeBoundingBox();
     
    var XmaxYmaxZmax = target.geometry.boundingBox.max.clone();
    var XmaxYmaxZmin = target.geometry.boundingBox.max.clone().setZ(target.geometry.boundingBox.min.z);   
    var XmaxYminZmax = target.geometry.boundingBox.max.clone().setY(target.geometry.boundingBox.min.y);
    var XmaxYminZmin = target.geometry.boundingBox.min.clone().setX(target.geometry.boundingBox.max.x);
    
    var XminYmaxZmax = target.geometry.boundingBox.max.clone().setX(target.geometry.boundingBox.min.x);
    var XminYmaxZmin = target.geometry.boundingBox.min.clone().setY(target.geometry.boundingBox.max.y);   
    var XminYminZmax = target.geometry.boundingBox.min.clone().setZ(target.geometry.boundingBox.max.z);
    var XminYminZmin = target.geometry.boundingBox.min.clone();
    
    
    var FrontLeftLineGeometry = new THREE.Geometry();
    FrontLeftLineGeometry.vertices.push( XminYmaxZmax );
    FrontLeftLineGeometry.vertices.push( XmaxYmaxZmax );
    FrontLeftLineGeometry.vertices.push( XmaxYminZmax );
    FrontLeftLineGeometry.vertices.push( XminYminZmax );   
    FrontLeftLineGeometry.vertices.push( XminYmaxZmax );
    FrontLeftLineGeometry.vertices.push( XminYmaxZmin );
    FrontLeftLineGeometry.vertices.push( XminYminZmin );
    FrontLeftLineGeometry.vertices.push( XminYminZmax ); 
    this.FrontLeftLine = new THREE.Line( FrontLeftLineGeometry, new THREE.LineBasicMaterial( {color: 0x0000ff} ) );   	
    this.add(this.FrontLeftLine);
    
    var BackRightLineGeometry = new THREE.Geometry();
    BackRightLineGeometry.vertices.push( XmaxYmaxZmin );
    BackRightLineGeometry.vertices.push( XminYmaxZmin );
    BackRightLineGeometry.vertices.push( XminYminZmin );
    BackRightLineGeometry.vertices.push( XmaxYminZmin );  
    BackRightLineGeometry.vertices.push( XmaxYmaxZmin );
    BackRightLineGeometry.vertices.push( XmaxYmaxZmax );
    BackRightLineGeometry.vertices.push( XmaxYminZmax );
    BackRightLineGeometry.vertices.push( XmaxYminZmin ); 
    this.BackRightLine = new THREE.Line( BackRightLineGeometry, new THREE.LineBasicMaterial( {color: 0x0000ff} ) );   	
    this.add(this.BackRightLine);
};

OSMEX.BoundingBox.prototype = Object.create( THREE.Object3D.prototype );

OSMEX.BoundingBox.prototype.setVisibility = function ( visibility ) {
    
    this.traverse( function( object ) { object.visible = visibility; } );
};
