<script>
  bxCartId = {$bxCartId nofilter};
  bxImgDir = "{$bxImgDir nofilter}";

  // more complete version of DOMContentLoaded, otherwise will not work for guest checkout with one page checkout
  var callback = function() {
    bxParcelPoint.init();
  };

  if (
    document.readyState === "complete" ||
    (document.readyState !== "loading" && !document.documentElement.doScroll)
  ) {
    callback();
  } else {
    document.addEventListener("DOMContentLoaded", callback);
  }
</script>
