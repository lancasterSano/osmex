var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.InterfaceScene = function ( camera ) {
    
    THREE.Scene.call( this );
    
    this.camera = camera;
};

OSMEX.InterfaceScene.prototype = Object.create( THREE.Scene.prototype );

OSMEX.InterfaceScene.prototype.updateMatrixWorld = function ( force ) {
    
    for (var i = 0, l = this.children.length; i < l; i++)
    {
        // to make 1 unit equals 4 pixels on the screen for interface scene
        var scale = 4 * this.camera.position.distanceTo(this.children[i].position) / this.camera.unitToPixelScale;
        
        this.children[i].scale.x = scale;
        this.children[i].scale.y = scale;
        this.children[i].scale.z = scale;
    }
    
    THREE.Scene.prototype.updateMatrixWorld.call(this, force);
};
