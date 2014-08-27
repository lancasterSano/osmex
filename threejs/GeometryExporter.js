/**
 * @author mrdoob / http://mrdoob.com/
 */

THREE.GeometryExporter = function () {};

THREE.GeometryExporter.prototype = {

	constructor: THREE.GeometryExporter,

	parse: function ( geometry ) {

		var output = {
			metadata: {
				version: 4.0,
				type: 'geometry',
				generator: 'GeometryExporter'
			}
		};

		var vertices = [];

		for ( var i = 0; i < geometry.vertices.length; i ++ ) {

			var vertex = geometry.vertices[ i ];
			vertices.push( vertex.x, vertex.y, vertex.z );

		}

		var faces = [];
		var uvs = [[]];
		var normals = [];
		var normalsHash = {};

		for ( var i = 0; i < geometry.faces.length; i ++ ) {

			var face = geometry.faces[ i ];

			var isTriangle = face instanceof THREE.Face3;
			var hasMaterial = false; // face.materialIndex !== undefined;
			var hasFaceUv = geometry.faceUvs[ 0 ][ i ] !== undefined;
			var hasFaceVertexUv = geometry.faceVertexUvs[ 0 ][ i ] !== undefined;
			var hasFaceNormal = false; // face.normal.length() > 0;
			var hasFaceVertexNormal = false; // face.vertexNormals[ 0 ] !== undefined;
			var hasFaceColor = false; // face.color;
			var hasFaceVertexColor = false; // face.vertexColors[ 0 ] !== undefined;

			var faceType = 0;

			faceType = setBit( faceType, 0, ! isTriangle );
			faceType = setBit( faceType, 1, hasMaterial );
			faceType = setBit( faceType, 2, hasFaceUv );
			faceType = setBit( faceType, 3, hasFaceVertexUv );
			faceType = setBit( faceType, 4, hasFaceNormal );
			faceType = setBit( faceType, 5, hasFaceVertexNormal );
			faceType = setBit( faceType, 6, hasFaceColor );
			faceType = setBit( faceType, 7, hasFaceVertexColor );

			faces.push( faceType );

			if ( isTriangle ) {

				faces.push( face.a, face.b, face.c );

			} else {

				faces.push( face.a, face.b, face.c, face.d );

			}

			if ( hasMaterial ) {

				faces.push( face.materialIndex );

			}

			if ( hasFaceUv ) {

				var uv = geometry.faceUvs[ 0 ][ i ];
				uvs[ 0 ].push( uv.u, uv.v );   
			}
			
			if ( hasFaceVertexUv ) {
                                
				var vertexUvs = geometry.faceVertexUvs[ 0 ][ i ];

				if ( isTriangle ) {

					uvs[ 0 ].push(vertexUvs[ 0 ].u, vertexUvs[ 0 ].v);
					uvs[ 0 ].push(vertexUvs[ 1 ].u, vertexUvs[ 1 ].v);
					uvs[ 0 ].push(vertexUvs[ 2 ].u, vertexUvs[ 2 ].v);

				} else {

					uvs[ 0 ].push(vertexUvs[ 0 ].u, vertexUvs[ 0 ].v);
					uvs[ 0 ].push(vertexUvs[ 1 ].u, vertexUvs[ 1 ].v);
					uvs[ 0 ].push(vertexUvs[ 2 ].u, vertexUvs[ 2 ].v);
                                        uvs[ 0 ].push(vertexUvs[ 3 ].u, vertexUvs[ 3 ].v);

				}

			}

			if ( hasFaceNormal ) {

				var faceNormal = face.normal;
				faces.push( getNormalIndex( faceNormal.x, faceNormal.y, faceNormal.z ) );

			}

			if ( hasFaceVertexNormal ) {

				var vertexNormals = face.vertexNormals;

				if ( isTriangle ) {

					faces.push(
						getNormalIndex( vertexNormals[ 0 ].x, vertexNormals[ 0 ].y, vertexNormals[ 0 ].z ),
						getNormalIndex( vertexNormals[ 1 ].x, vertexNormals[ 1 ].y, vertexNormals[ 1 ].z ),
						getNormalIndex( vertexNormals[ 2 ].x, vertexNormals[ 2 ].y, vertexNormals[ 2 ].z )
					);

				} else {

					faces.push(
						getNormalIndex( vertexNormals[ 0 ].x, vertexNormals[ 0 ].y, vertexNormals[ 0 ].z ),
						getNormalIndex( vertexNormals[ 1 ].x, vertexNormals[ 1 ].y, vertexNormals[ 1 ].z ),
						getNormalIndex( vertexNormals[ 2 ].x, vertexNormals[ 2 ].y, vertexNormals[ 2 ].z ),
						getNormalIndex( vertexNormals[ 3 ].x, vertexNormals[ 3 ].y, vertexNormals[ 3 ].z )
					);

				}

			}

		}

		function setBit( value, position, enabled ) {

			return enabled ? value | ( 1 << position ) : value & ( ~ ( 1 << position) );

		}

		function getNormalIndex( x, y, z ) {

			var hash = x.toString() + y.toString() + z.toString();

			if ( normalsHash[ hash ] !== undefined ) {

				return normalsHash[ hash ];

			}

			normalsHash[ hash ] = normals.length / 3;
			normals.push( x, y, z );

			return normalsHash[ hash ];

		}

		output.vertices = vertices;
		output.normals = normals;
		output.uvs = uvs;
		output.faces = faces;

		//

		return output;

	}

};
