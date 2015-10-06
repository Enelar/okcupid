var warmup_obj =
{
  wait: true,
  config: "/api/phoxy",
  skip_initiation: true,
  sync_cascade: true,
  OnWaiting: function()
  {
    phoxy._.EarlyStage.sync_require[0] = "/enjs.js";

    phoxy._.EarlyStage.sync_require.push("//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js");

    phoxy.state.early.optional.lazy = 4;
    phoxy.state.first_page = true;

    phoxy._.EarlyStage.EntryPoint();
  },
  OnBeforeCompile: function()
  {
    requirejs.config(
    {
      baseUrl: phoxy.config['js_dir'],
    });

    $('head').append
    (
      '<link rel="subresource" href="/api/main">'
      + '<link rel="prefetch" href="/api/main">'
    );
  },
  OnAfterCompile: function()
  {
    phoxy.Config()['api_dir'] = '/' + phoxy.Config()['api_dir'];
    phoxy.Config()['ejs_dir'] = '/' + phoxy.Config()['ejs_dir'];
    phoxy.Config()['js_dir'] = '/' + phoxy.Config()['js_dir'];
    EJS.IsolationDepth = 3;

    $('head').append
    (
      '<link rel="subresource" href="//cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.0.0/semantic.min.js">'
      + '<link rel="prefetch" href="//cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.0.0/semantic.min.js">'
    );

    phoxy.ChangeHash = function(url)
    {
      if (typeof url !== 'string')
      {
        arr = url = url.slice(0)
        url = arr.shift(1);
        if (arr.length > 0)
          url += "(" + arr.join() + ")";
      }

      console.log(9, "History push", url);
      if (url[0] != '/')
        url = '/' + url;
      history.pushState({}, document.title, url);

      $('body').trigger('new.page');
      $('.modal').modal('hide');
      return false;
    }

    phoxy.Override('ChangeURL', function(url)
    {
      var ret = arguments.callee.origin.apply(this, arguments);
      //analytics.page();
      return ret;
    })

    var not_found = phoxy.ApiAnswer;
    phoxy.ApiAnswer = function(data)
    {
      if (data["error"] === 'Module not found'
          || data["error"] === "Unexpected RPC call (Module handler not found)")
      {
        $('.removeafterload').remove();
        return phoxy.ApiRequest("utils/page404");
      }
      return not_found.apply(this, arguments);
    }

    // Allow force non-caching requests on form post and any information update
    var direct_request = phoxy.ApiRequest;
    phoxy.ApiRequest = function(origin, callback, direct)
    {
      if (typeof direct === 'undefined' || direct !== true)
        return direct_request.apply(this, arguments);
      if (typeof origin == 'string')
        origin += "?direct";
      else
        origin[0] += "?direct";

      return direct_request.call(this, origin, callback);
    }

    phoxy.Log(3, "Phoxy ready. Starting");
  },
  OnBeforeFirstApiCall: function()
  {
    requirejs.config({baseUrl: phoxy.Config()['js_dir']});

    // Enable jquery in EJS context
    var origin_hook = EJS.Canvas.prototype.hook_first;
    EJS.Canvas.prototype.hook_first = function()
    {
      return $(origin_hook.apply(this, arguments));
    }
  },
  OnInitialClientCodeComplete: function()
  {
    phoxy.Log(3, "Initial handlers complete");
    $('.removeafterload').remove();
    $('body').trigger('initialrender');

    (function()
    {
      $(window).height();
      phoxy.Defer(arguments.callee, 500);
    })();
  }
  ,
  OnFirstPageRendered: function()
  {
    phoxy.Log(3, "First page rendered");
    phoxy.state.first_page = false;
  }
};

if (typeof phoxy.prestart === 'undefined')
  phoxy = warmup_obj;
else
{
  phoxy.prestart = warmup_obj;
  phoxy.prestart.OnWaiting();
}

if (typeof YOUR_ANALYTICS_KEY !== 'undefined')
  if(window.analytics=window.analytics||[],window.analytics.included)window.console&&console.error&&console.error("analytics.js included twice");else{window.analytics.included=!0,window.analytics.methods=["identify","group","track","page","pageview","alias","ready","on","once","off","trackLink","trackForm","trackClick","trackSubmit"],window.analytics.factory=function(a){return function(){var n=Array.prototype.slice.call(arguments);return n.unshift(a),window.analytics.push(n),window.analytics}};for(var i=0;i<window.analytics.methods.length;i++){var key=window.analytics.methods[i];window.analytics[key]=window.analytics.factory(key)}window.analytics.load=function(a){var n=document.createElement("script");n.type="text/javascript",n.async=!0,n.src=("https:"===document.location.protocol?"https://":"http://")+"cdn.segment.com/analytics.js/v1/"+a+"/analytics.min.js";var t=document.getElementsByTagName("script")[0];t.parentNode.insertBefore(n,t)},window.analytics.SNIPPET_VERSION="2.0.9",
    window.analytics.load(YOUR_ANALYTICS_KEY)}

(function()
{
  if (typeof require === 'undefined')
    return setTimeout(arguments.callee, 50);
  clearTimeout(require_not_loading);

  require(['/phoxy/phoxy.js'], function(){});
})();

var require_not_loading = setTimeout(function()
{
  var d = document;
  var js = d.createElement("script");
  js.type = "text/javascript";
  js.src = "//cdnjs.cloudflare.com/ajax/libs/require.js/2.1.15/require.min.js";
  d.head.appendChild(js);

  console.log("Require loading timeout");
}, 5000);

// Loading animation
(function()
{
  if (typeof phoxy._ == 'undefined')
    return setTimeout(arguments.callee, 10);

  var percents = phoxy._.EarlyStage.LoadingPercentage();
  var element = document.getElementById('percent');

  if (element == null)
    return;
  element.style.width = percents + "px";
  element.style.opacity = percents / 100 + 0.5;
  setTimeout(arguments.callee, 50);

  if (percents == 100)
    $('.removeafterload').css('opacity', 0);
})();