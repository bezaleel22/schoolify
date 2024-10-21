(function (factory) {
  "use strict";
  /* global define */
  if (typeof define === "function" && define.amd) {
    // AMD. Register as an anonymous module.
    define(["jquery"], factory);
  } else {
    // Browser globals: jQuery
    factory(jQuery);
  }
})(function ($) {
  "use strict";

  var md = window.markdownit();

  var $preview = $("<div />");

  $.extend($.summernote.plugins, {
    /**
     * @param {Object} context - context object has status of editor.
     */
    markdown: function (context) {
      var self = this;

      // ui has renders to build ui elements.
      //  - you can create a button with `ui.button`
      var ui = $.summernote.ui;

      function togglePreview(status) {
        var layout = context.layoutInfo;
        var icon = $(".fa-mkpreview", layout.toolbar);
        if (status) {
          self.isPreview = true;
          self.lastCode = context.code();

          context.invoke("codeview.deactivate");

          icon.addClass("glyphicon-eye-close");
          icon.removeClass("glyphicon-eye-open");
        } else {
          self.isPreview = false;
          context.code(self.lastCode);

          layout.codable.hide();
          context.invoke("codeview.activate");
          layout.codable.show();

          icon.addClass("glyphicon-eye-open");
          icon.removeClass("glyphicon-eye-close");
        }

        icon
          .closest(".note-btn-group")
          .find(".disabled")
          .removeAttr("disabled")
          .removeClass("disabled");
        context.layoutInfo.editable.removeAttr("contenteditable");
      }

      self.isPreview = false;

      context.memo("button.markdown", function () {
        var button = ui.button({
          contents: '<i class="glyphicon fa-mkpreview" />',
          tooltip: "预览",
          click: function () {
            togglePreview(!self.isPreview);

            var res = md.render(context.code());
            context.layoutInfo.editable.html(res);
          },
        });

        return button.render();
      });

      // This events will be attached when editor is initialized.
      this.events = {
        // This will be called after modules are initialized.
          "summernote.init": function (we, e) {
          self.lastCode = context.code();
          // $preview.html(md.render(layoutInfo.holder().code()));
          console.log('summernote initialized', we, e);
        },
        // This will be called when user releases a key on editable.
        "summernote.keyup": function (we, e) {
          console.log("summernote keyup", we, e);
        },
      };
    },
  });
});
