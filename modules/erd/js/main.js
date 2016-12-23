(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.erd = {
    attach: function (context, settings) {
      $('.erd-container').once('erd-init').each(erdInit, [settings]);
    }
  };

  $.widget('custom.erdautocomplete', $.ui.autocomplete, {
    _create: function () {
      this._super();
      this.widget().menu('option', 'items', '> :not(.erd-autocomplete-category)');
    },
    _renderMenu: function (ul, items) {
      var that = this,
        current_category = '';
      $.each( items, function (index, item) {
        var li;
        if (item.data.type_label != current_category) {
          ul.append("<li class='erd-autocomplete-category'>" + item.data.type_label + 's</li>');
          current_category = item.data.type_label;
        }
        li = that._renderItemData(ul, item);
        if (item.data.type_label) {
          li.attr('aria-label', item.data.type_label + 's : ' + item.data.label );
        }
      });
    }
  });

  function erdInit (settings) {
    var line_styles = [null, 'orthogonal', 'manhattan', 'metro'];
    var line_style_index = 0;

    var paper;
    var graph = createGraph(this);

    var entity_bundle = getDefaultJointEntity();

    var entity_type = entity_bundle.clone();
    entity_type.attr('.outer, .inner, .attribute-background/fill', '#C46A2D');
    entity_type.attr('.outer, .inner, .attribute-background/stroke', '#C46A2D');

    var entity_label = entity_bundle.clone();
    entity_label.attr('.outer, .inner, .attribute-background/fill', '#53C42D');
    entity_label.attr('.outer, .inner, .attribute-background/stroke', '#53C42D');

    initAutocomplete();

    // Add a generic Entity to the screen when the label button is clicked.
    $('.erd-label').click(function () {
      addLabel();
    });

    // When a Label's text is clicked, show a prompt to change the text.
    // Inline editing is preferable to this, but this was simple in the short
    // term.
    $(this).on('click', '.erd-label .label', function () {
      var model_id = $(this).closest('[model-id]').attr('model-id');
      var model = graph.get('cells').get(model_id);
      var text = prompt(Drupal.t('Please enter new label text'), model.attr('.label/text'));
      if (text && text.length > 0) {
        model.attr('.label/text', Drupal.checkPlain(text));

        // Re-adjust erd.Entity widths.
        reAdjustWidths();
      }
    });

    // Saves the SVG onscreen as an image.
    $('.erd-save').click(function() {
      var $container = $('.erd-container');
      var svg_clone = paper.svg.cloneNode(true);
      $(svg_clone).find('.port, .remove-entity, .link-tools, .marker-vertices, .marker-arrowheads, .connection-wrap').remove();
      $(svg_clone).attr('width', $container.outerWidth());
      $(svg_clone).attr('height', $container.outerHeight());

      var serializer = new XMLSerializer();
      var svg = serializer.serializeToString(svg_clone);

      var data = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg)));

      var image = new Image();
      image.src = data;
      image.onload = function() {
        var canvas = document.createElement('canvas');
        canvas.width = image.width;
        canvas.height = image.height;
        var context = canvas.getContext('2d');
        context.drawImage(image, 0, 0);

        var png = canvas.toDataURL('image/png');

        var a = document.createElement('a');
        a.href = png;
        a.download = 'erd.png';
        a.click();
      };
    });

    // Show machine names or labels when this button is clicked.
    $('.erd-machine-name').click(function() {
      $(this).toggleClass('active');
      var display = $(this).hasClass('active') ? 'id' : 'label';

      graph.get('cells').each(function (cell) {
        if (cell.has('erd.bundle')) {
          var bundle = cell.get('erd.bundle');
          cell.attr('.label/text', Drupal.checkPlain(bundle[display]));

          // Change the display of fields based on our current active state.
          if (bundle.fields) {
            var field, text_class;
            for (var field_name in bundle.fields) {
              field = bundle.fields[field_name];
              text_class = 'attribute-' + field_name;

              cell.attr('.' + text_class + '/text', Drupal.checkPlain(field[display]));
            }
          }
        }
        else if (cell.has('erd.type')) {
          var type = cell.get('erd.type');
          cell.attr('.label/text', Drupal.checkPlain(type[display]));
        }
      });

      reAdjustWidths();
    });

    // When a user clicks the line-style button, cycle through our available
    // link styles.
    $('.erd-line-style').click(function () {
      if (line_style_index < line_styles.length - 1) {
        ++line_style_index;
      }
      else {
        line_style_index = 0;
      }

      // Change the line style for the default paper link.
      if (line_styles[line_style_index]) {
        paper.options.defaultLink.set('router', {name: line_styles[line_style_index]});
      }
      else {
        paper.options.defaultLink.unset('router');
      }

      // Change line styles for all on-screen links.
      graph.get('cells').each(function (cell) {
        if (cell.get('type') == 'erd.Line' || cell.get('type') == 'link') {
          if (line_styles[line_style_index]) {
            cell.set('router', {name: line_styles[line_style_index]});
          }
          else {
            cell.unset('router');
          }

          // Hotfix for badly rendered metro/manhattan links.
          cell.attr('.connection/fill', 'none');
        }
      });
    });

    // When the remove icon (trash can) is clicked on an Entity, remove it
    // from the graph.
    $(this).on('click', '.remove-entity', function () {
      var model_id = $(this).closest('[model-id]').attr('model-id');
      graph.get('cells').get(model_id).remove();
    });

    function reAdjustWidths () {
      graph.get('cells').each(function (cell) {
        if (cell.get('type') == 'erd.Entity') {
          // Calculate our max string length.
          var title_length = 0;
          var attribute_length = 0;
          var attrs = cell.get('attrs');
          for (var i in attrs) {
            if (attrs[i].text) {
              var length = attrs[i].text.length;
              if (i.indexOf('.attribute') === 0 && length > attribute_length) {
                attribute_length = length;
              }
              else if (i == '.label' && length > title_length)  {
                title_length = length;
              }
            }
          }

          // Determine our necessary width based on known font sizes.
          title_length = title_length * 8;
          attribute_length = attribute_length * 6;
          var width = title_length > attribute_length ? title_length : attribute_length;
          if (width < 150) {
            width = 150;
          }

          // Resize our rectangles and re-position the right magnet link.
          cell.attr('.outer, .inner, .attribute-background/points', width + ',0 ' + width + ',60 0,60 0,0');
          cell.attr('.attribute-background/points',  width + ',0 ' + width + ',20 0,20 0,0');
          cell.attr('.port-right/ref-x', width);
        }
      });
    }

    function initAutocomplete () {
      var source_types = [];
      var source_bundles = [];

      for (var i in drupalSettings.erd.entities) {
        source_types.push({
          value: drupalSettings.erd.entities[i].label,
          data: drupalSettings.erd.entities[i]
        });
        for (var j in drupalSettings.erd.entities[i].bundles) {
          source_bundles.push({
            value: Drupal.checkPlain(drupalSettings.erd.entities[i].bundles[j].label),
            data: drupalSettings.erd.entities[i].bundles[j]
          });
        }
      }

      var source = $.merge(source_types, source_bundles);

      $('.erd-search input').erdautocomplete({
        source: source,
        select: function (suggestion, ui) {
          $('.erd-search input').attr('value', '');
          if (ui.item.data.type == 'type') {
            addType(ui.item.data);
          }
          else {
            addBundle(ui.item.data);
          }

          // Re-adjust erd.Entity widths.
          reAdjustWidths();
        }
      });
    }

    function createGraph (element) {
      var graph = new joint.dia.Graph();

      var $paper_el = $(element);

      paper = new joint.dia.Paper({
        el: $paper_el,
        width: '100%',
        height: 650,
        gridSize: 1,
        model: graph,
        linkPinning: false,
        linkConnectionPoint: joint.util.shapePerimeterConnectionPoint,
        defaultLink: new joint.dia.Link({
          attrs: {
            '.marker-target': { fill: '#000000', stroke: '#000000', d: 'M 10 0 L 0 5 L 10 10 z' },
            '.connection': { fill: 'none' }
          }
        })
      });

      var panAndZoom = svgPanZoom($paper_el[0].childNodes[0],
        {
          viewportSelector: $paper_el[0].childNodes[0].childNodes[0],
          fit: false,
          zoomScaleSensitivity: 0.1,
          mouseWheelZoomEnabled: false,
          panEnabled: false
        });

      paper.on('blank:pointerdown', function (evt, x, y) {
        panAndZoom.enablePan();
      });

      paper.on('cell:pointerup blank:pointerup', function(cellView, event) {
        panAndZoom.disablePan();
      });

      $('.erd-zoom').click(function () {
        panAndZoom.zoomIn();
      });

      $('.erd-unzoom').click(function () {
        panAndZoom.zoomOut();
      });

      return graph;
    }

    function getDefaultJointEntity () {
      return new joint.shapes.erd.Entity({
        markup:
        '<g class="rotatable"><g class="scalable"><polygon class="outer"/><polygon class="inner"/></g><text/><text class="label"/><circle class="port port-left"/><circle class="port port-right"/></g>' +
        '<a class="remove-entity"><svg fill="#F7F7F7" height="20" width="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"> <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/> <path d="M0 0h24v24H0z" fill="none"/> </svg></a>',

        attrs: {
          text: {
            text: '',
            fill: '#ffffff',
            'letter-spacing': 0,
            style: { 'text-shadow': '1px 0 1px #333333' }
          },
          '.connection': {
            fill: 'none'
          },
          '.label': {
            text: ''
          },
          '.outer, .inner, .attribute-background': {
            fill: '#2AA8A0', stroke: '#2AA8A0', 'stroke-width': 2,
            points: '150,0 150,60 0,60 0,0',
            filter: { name: 'dropShadow',  args: { dx: 0.5, dy: 2, blur: 2, color: '#333333' }}
          },
          '.port': {
            magnet: 'active',
            r: 7,
            fill: '#F7F7F7',
            'stroke-width': 1,
            stroke: '#CCCCCC',
            ref: '.outer', 'ref-x': 0, 'ref-y': .5
          },
          '.port-right': {
            'ref-x': 150, 'ref-y': .5
          },
          '.attribute': {
            text: '',
            'font-size': 12,
            ref: '.outer', 'ref-x': .5, 'ref-y': 65,
            'x-alignment': 'middle', 'y-alignment': 'middle'
          },
          '.attribute-background': {
            fill: '#31d0c6', stroke: '#289E97',
            points: '150,0 150,20 0,20 0,0',
            ref: '.outer', 'ref-x': 0, 'ref-y': 60
          }
        }
      });
    }

    function addLabel () {
      var cell = entity_label.clone().translate(0, 0).attr('.label/text', 'Change me');
      var markup = cell.get('markup');

      markup = '<g class="erd-label">' + markup + '</g>';
      cell.set('markup', markup);
      graph.addCell(cell);
    }

    function addType (type) {
      var display = $('.erd-machine-name').hasClass('active') ? 'id' : 'label';
      var cell = entity_type.clone().translate(0, 0).attr('.label/text', Drupal.checkPlain(type[display]));

      cell.set({identifier: 'type:' + type.id}, { silent: true });
      graph.addCell(cell);

      cell.set('erd.type', type);

      // Refresh all links on screen.
      refreshLinks();
    }

    function addBundle (bundle) {
      var display = $('.erd-machine-name').hasClass('active') ? 'id' : 'label';
      var cell = entity_bundle.clone().translate(0, 0).attr('.label/text', Drupal.checkPlain(bundle[display]));

      cell.set({identifier: 'bundle:' + bundle.id}, { silent: true });
      var markup = cell.get('markup');

      // Add elements to our markup.
      if (bundle.fields) {
        var field, text_class, background_class, background_y, text_y;
        var i = 0;
        for (var field_name in bundle.fields) {
          field = bundle.fields[field_name];
          text_class = 'attribute-' + Drupal.checkPlain(field_name);
          background_class = 'attribute-background-' + Drupal.checkPlain(field_name);
          background_y = cell.attr('.attribute-background/ref-y') + (i * 20);
          text_y = cell.attr('.attribute/ref-y') + (i * 20);

          markup += '<polygon class="attribute-background ' + background_class + '"/>';
          markup += '<text class="attribute ' + text_class + '"/>';

          cell.attr('.' + text_class + '/text', Drupal.checkPlain(field[display]));
          cell.attr('.' + text_class + '/ref-y', text_y);
          cell.attr('.' + text_class + '/ref-x', 5);
          cell.attr('.' + background_class + '/ref-y', background_y);

          cell.set('erd.bundle', bundle);

          ++i;
        }

        cell.set({markup: markup});
      }

      graph.addCell(cell);

      // Refresh all links on screen.
      refreshLinks();
    }

    function createLink (source, target, label) {
      var settings = {
        source: source,
        target: target,
        attrs: {
          '.marker-target': { fill: '#000000', stroke: '#000000', d: 'M 10 0 L 0 5 L 10 10 z' },
          '.connection': { fill: 'none' }
        }
      };

      if (line_styles[line_style_index]) {
        settings.router = {name: line_styles[line_style_index]};
      }

      var link = new joint.shapes.erd.Line(settings);

      link.addTo(graph).set('labels', [{
        position: 0.5,
        attrs: {
          text: {
            text: label, fill: '#f6f6f6',
            'font-family': 'sans-serif', 'font-size': 10,
            style: { 'text-shadow': '1px 0 1px #333333' }
          },
          rect: { stroke: '#618eda', 'stroke-width': 20, rx: 5, ry: 5 } }
      }]);
    }

    // Builds and refreshs links for all on-screen elements.
    function refreshLinks () {
      for (var i in drupalSettings.erd.links) {
        var link = drupalSettings.erd.links[i];
        var from = graph.get('cells').findWhere({ identifier: link.from });
        // This may not be on-screen.
        if (from) {
          for (var j in link.targets) {
            var to = graph.get('cells').findWhere({ identifier: link.targets[j] });
            if (to && from !== to) {
              createLink({id: from.id, selector: link.from_selector}, {id: to.id}, link.label);
            }
          }
        }
      }
    }
  }

}(jQuery, Drupal, drupalSettings));
