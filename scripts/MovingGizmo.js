var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.MovingGizmo = function ( ) {
    
    THREE.Object3D.call( this );
    
    this.target = null;
	
    this.AxisX = new OSMEX.MovingArrow( new THREE.Vector3( 1, 0, 0 ), 0xff0000 );  
    this.AxisY = new OSMEX.MovingArrow( new THREE.Vector3( 0, 1, 0 ), 0x00ff00 );
    this.AxisZ = new OSMEX.MovingArrow( new THREE.Vector3( 0, 0, 1 ), 0x0000ff );
    
    this.add(this.AxisX);
    this.add(this.AxisY);
    this.add(this.AxisZ);
    
    this.PlaneYZ = new OSMEX.MovingGizmoPlane( new THREE.Vector3( 1, 0, 0 ), 0xff0000 );
    this.PlaneXZ = new OSMEX.MovingGizmoPlane( new THREE.Vector3( 0, 1, 0 ), 0x00ff00 );
    this.PlaneXY = new OSMEX.MovingGizmoPlane( new THREE.Vector3( 0, 0, 1 ), 0x0000ff );
    
    this.add(this.PlaneYZ);
    this.add(this.PlaneXZ);
    this.add(this.PlaneXY);
    
    this.setTarget(null);
};

OSMEX.MovingGizmo.prototype = Object.create( THREE.Object3D.prototype );

OSMEX.MovingGizmo.prototype.setTarget = function ( target ) {
    
    this.target = target;
    
    var arrowMoveFunc = null;
    var planeMoveFunc = null;
    
    var visibility = false;
    
    if ( target ) {
        
        visibility = true;
        
        arrowMoveFunc = function(target) { return function(position) {
                
            if (this.dir.x === 1)      target.position.x = position.x;
            else if (this.dir.y === 1) target.position.y = position.y;
            else if (this.dir.z === 1) target.position.z = position.z;
                         
        } }(this.target);
    
        planeMoveFunc = function(target) { return function(position) { 
                
            if (this.dir.x === 1) {
                
                target.position.y = position.y;
                target.position.z = position.z;
            }
            else if (this.dir.y === 1) {
                
                target.position.x = position.x;
                target.position.z = position.z;
            }
            else if (this.dir.z === 1) {
                
                target.position.x = position.x;
                target.position.y = position.y;
            }
                          
        } }(this.target);
                        
    }
    
    this.traverse( function( object ) { object.visible = visibility } );
    
    this.AxisX.moveFunc = arrowMoveFunc;
    this.AxisY.moveFunc = arrowMoveFunc;
    this.AxisZ.moveFunc = arrowMoveFunc;
    
    this.PlaneYZ.moveFunc = planeMoveFunc;
    this.PlaneXZ.moveFunc = planeMoveFunc;
    this.PlaneXY.moveFunc = planeMoveFunc;  
}

OSMEX.MovingGizmo.prototype.update = function ( ) {
    
    if(this.target){  
        
        this.position.copy(this.target.position);
    }
}
