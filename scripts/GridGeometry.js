var OSMEX = OSMEX || { REVISION: '1' };

OSMEX.GridGeometry = function ( width, height, widthSegments, heightSegments ) {
    
    THREE.Geometry.call( this );

    var ix, iz,
    width_half = width / 2,
    height_half = height / 2,
    gridX = widthSegments || 1,
    gridZ = heightSegments || 1,
    gridX1 = gridX + 1,
    gridZ1 = gridZ + 1,
    segment_width = width / gridX,
    segment_height = height / gridZ,
    normal = new THREE.Vector3( 0, 1, 0 );

    for ( iz = 0; iz < gridZ1; iz ++ ) {
        
        var z = iz * segment_height - height_half;
		
        for ( ix = 0; ix < gridX1; ix ++ ) {
            
            var x = ix * segment_width - width_half;
			
            this.vertices.push( new THREE.Vector3( x, 0, z ) );
        }
    }

    for ( iz = 0; iz < gridZ; iz ++ ) {
        
        for ( ix = 0; ix < gridX; ix ++ ) {
            
            var a = ix + gridX1 * iz;
            var b = ix + gridX1 * ( iz + 1 );
            var c = ( ix + 1 ) + gridX1 * ( iz + 1 );
            var d = ( ix + 1 ) + gridX1 * iz;

            var face = new THREE.Face4( a, b, c, d );
            face.normal.copy( normal );
            face.vertexNormals.push( normal.clone(), normal.clone(), normal.clone(), normal.clone() );

            this.faces.push( face );
            this.faceVertexUvs[ 0 ].push( [
                new THREE.UV( 0.0, 1.0 ),
                new THREE.UV( 0.0, 0.0 ),
                new THREE.UV( 1.0, 0.0 ),
                new THREE.UV( 1.0, 1.0 )
                ] );

        }
    }

    this.computeCentroids();
};

OSMEX.GridGeometry.prototype = Object.create( THREE.Geometry.prototype ); 
