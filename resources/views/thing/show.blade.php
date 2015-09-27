@extends('layouts.master')

@section('extrahead')
    <link rel="stylesheet" href="https://developer.api.autodesk.com/viewingservice/v1/viewers/style.css"
          type="text/css">
    <script src="https://developer.api.autodesk.com/viewingservice/v1/viewers/viewer3D.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
@endsection
@section('extrabottom')
    <script>
        function initialize() {
            var timer = setInterval(function () {
                $.ajax({
                    url: "{{ $pollUrl }}",
                    type: "GET",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Authorization', 'Bearer {{ $authToken }}');
                    },
                    success: function (data) {
                        console.log(data);

                        if (data.status == 'success') {
                            clearInterval(timer);
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
                    },
                    dataType: "json",
                    timeout: 2000
                })
            }, 1000);
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
@endsection

@section('content')
    <nav class="navbar navbar-default">
        <div class="row">
            <div class="nav-brand">
                <a href="/"><img src="/images/logo.png"></a>
            </div>
            <div class="form-group col-md-12 ">
                <form action="/search" method="POST">
                    <input type="text" name="query" class="form-control input-normal"
                           placeholder="Search for 3D models"/>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                </form>
            </div>
        </div>
    </nav>
    <section>
        <div class="container">
            <div class="row">
                <div class=".col-md-12">
                    <h1>
                        {{ $name }}
                    </h1>

                    <p>
                        {{ $description }}
                    </p>

                    <p>
                        <a href="{{ $dl }}" style="padding-bottom: 10px;">
                            <button type="button" class="btn btn-primary">Download</button>
                        </a>
                    </p>
                    <div id="viewer" style="position:absolute; width:60%; height:40%;"></div>
                </div>
            </div>
        </div>
    </section>
@endsection