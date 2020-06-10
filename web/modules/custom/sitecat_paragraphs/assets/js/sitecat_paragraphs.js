/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal) {

  'use strict';

  /**
   * Handle the background looping youtube videos.
   */
  Drupal.behaviors.sitecatParagraphsYoutubeBgVideo = {
    attach: function (context, settings) {

      var $video = $('.paragraph--type--hero iframe', context);
      if ($video.length === 0) {
        return;
      }
      var player;
      var YTinterval = setInterval(
        function() {
          if (typeof YT !== 'undefined') {
            clearInterval(YTinterval);
            var id = $video.attr('id');
            player = new YT.Player(id, {
              events: {
                'onReady': onVideoPlayerReady,
                'onStateChange': onVideoPlayerReady
              }
            });
          }
        }, 200);
      function onVideoPlayerReady(event) {
        if (event.data === YT.PlayerState.ENDED) {
          player.playVideo();
        }
      }
      var adjustLayout = function () {
        $video.css('height', $video.width() / (16 / 9));
        $video.css('margin-top', -64);
      };
      $(window).resize(_.debounce(adjustLayout, 200));
      adjustLayout();
    }
  };

})(jQuery, Drupal);
