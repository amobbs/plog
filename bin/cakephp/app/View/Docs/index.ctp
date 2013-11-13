<link href='//fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'/>
<link href='/assets/api/css/highlight.default.css' media='screen' rel='stylesheet' type='text/css'/>
<link href='/assets/api/css/screen.css' media='screen' rel='stylesheet' type='text/css'/>
<script type="text/javascript" src="/assets/api/lib/shred.bundle.js"></script>
<script src='/assets/api/lib/jquery-1.8.0.min.js' type='text/javascript'></script>
<script src='/assets/api/lib/jquery.slideto.min.js' type='text/javascript'></script>
<script src='/assets/api/lib/jquery.wiggle.min.js' type='text/javascript'></script>
<script src='/assets/api/lib/jquery.ba-bbq.min.js' type='text/javascript'></script>
<script src='/assets/api/lib/handlebars-1.0.0.js' type='text/javascript'></script>
<script src='/assets/api/lib/underscore-min.js' type='text/javascript'></script>
<script src='/assets/api/lib/backbone-min.js' type='text/javascript'></script>
<script src='/assets/api/lib/swagger.js' type='text/javascript'></script>
<script src='/assets/api/swagger-ui.js' type='text/javascript'></script>
<script src='/assets/api/lib/highlight.7.3.pack.js' type='text/javascript'></script>

<div id='header'>
    <div class="swagger-ui-wrap">
        <h1 style="float: left; margin-top: 0px;">
            MediaHub Presentation Log API
        </h1>
        <form id='api_selector'>
            <div class='input'><input placeholder="http://example.com/api" id="input_baseUrl" name="baseUrl" type="text"/></div>
            <div class='input'><a id="explore" href="#">Explore</a></div>
        </form>
        <br/><br/>
    </div>
</div>

<div id="message-bar" class="swagger-ui-wrap">
    &nbsp;
</div>

<div id="swagger-ui-container" class="swagger-ui-wrap">
</div>

<script type="text/javascript">
    $(function () {
        window.swaggerUi = new SwaggerUi({
            url: "<?php echo Router::url(array('controller'=>'Docs', 'action'=>'generateDocumentation'), true); ?>",
            dom_id: "swagger-ui-container",
            supportedSubmitMethods: ['get', 'post', 'put', 'delete'],
            onComplete: function(swaggerApi, swaggerUi){
                if(console) {
                    console.log("Loaded SwaggerUI")
                }
                $('pre code').each(function(i, e) {hljs.highlightBlock(e)});
            },
            onFailure: function(data) {
                if(console) {
                    console.log("Unable to Load SwaggerUI");
                    console.log(data);
                }
            },
            docExpansion: "none"
        });

        $('#input_apiKey').change(function() {
            var key = $('#input_apiKey')[0].value;
            console.log("key: " + key);
            if(key && key.trim() != "") {
                console.log("added key " + key);
                window.authorizations.add("key", new ApiKeyAuthorization("api_key", key, "query"));
            }
        })
        window.swaggerUi.load();
    });

</script>