var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.Landscape = function ( ) {
    
    THREE.ImmediateRenderObject.call( this );
    
    this.material = new THREE.MeshBasicMaterial( { wireframe: true, color: 0x000000 } );
    this.material.side = THREE.DoubleSide;
};

OSMEX.Landscape.prototype = Object.create( THREE.ImmediateRenderObject.prototype );

OSMEX.Landscape.prototype.immediateRenderCallback = function ( program, _gl, _frustum ) {

    if ( !this.__webglVertexBuffer ) this.__webglVertexBuffer = _gl.createBuffer();
    
    //_frustum.contains(object);
    
    var vertexArray = new Float32Array([
        0.0,  0.0,  0.0,
        30.0, 0.0,  0.0,
        0.0,  0.0, -30.0
    ]);

    _gl.bindBuffer( _gl.ARRAY_BUFFER, this.__webglVertexBuffer );
    _gl.bufferData( _gl.ARRAY_BUFFER, vertexArray, _gl.DYNAMIC_DRAW );
    _gl.enableVertexAttribArray( program.attributes.position );
    _gl.vertexAttribPointer( program.attributes.position, 3, _gl.FLOAT, false, 0, 0 );

    _gl.drawArrays( _gl.TRIANGLES, 0, 3 );
};
