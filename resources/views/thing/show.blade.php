<html>
<head>
    <title>Viewer</title>
    <link rel="stylesheet" href="https://developer.api.autodesk.com/viewingservice/v1/viewers/style.css"
          type="text/css">
    <script src="https://developer.api.autodesk.com/viewingservice/v1/viewers/viewer3D.min.js"></script>
</head>
<body>

<div id="viewer" style="position:absolute; width:90%; height:60%;"></div>
<script>
    function initialize() {
        var options = {
            'document': 'urn:{{ $urn }}',
            'env': 'AutodeskProduction',
            'getAccessToken': getToken,
            'refreshToken': getToken,
        };

        var viewerElement = document.getElementById('viewer');
        var viewer = new Autodesk.Viewing.Viewer3D(viewerElement, {});

        Autodesk.Viewing.Initializer(options, function () {
            viewer.initialize();
            loadDocument(viewer, options.document);
        });
    }

    // WARNING: UNSAFE CODE IN PRODUCTION
    function getToken() {
        return "{{ $authToken }}";
    }

    function loadDocument(viewer, documentId) {
        // Find the first 3d geometry and load that.
        Autodesk.Viewing.Document.load(documentId, function (doc) {// onLoadCallback
            var geometryItems = [];
            geometryItems = Autodesk.Viewing.Document.getSubItemsWithProperties(doc.getRootItem(), {
                'type': 'geometry',
                'role': '3d'
            }, true);
            if (geometryItems.length > 0) {
                viewer.load(doc.getViewablePath(geometryItems[0]));
            }
        }, function (errorMsg) {// onErrorCallback
            alert("Load Error: " + errorMsg);
        });
    }
</script>
</body>
</html>