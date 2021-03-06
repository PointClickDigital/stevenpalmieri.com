import ReactDOM from 'react-dom';
import React from 'react';

import { encodeLink, decodeLink } from './admin/encoder';
import Modal from './admin/modal';

// Prevent Divi Builder bug.
ready(() => {
	document.addEventListener( 'keydown', function(e) {
		for ( var target = e.target; target && target != this; target = target.parentNode ) {
			if ( target.matches( '.vlp-modal-container' ) ) {
				e.stopPropagation();
				break;
			}
		}
	}, false );
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

const addTextToEditor = function(editor, text) {
	text = ' ' + text + ' ';

	if (editor) {
		if (typeof window.tinyMCE !== 'undefined' && window.tinyMCE.get(editor) && !window.tinyMCE.get(editor).isHidden()) {
			window.tinyMCE.get(editor).focus(true);
			window.tinyMCE.activeEditor.selection.collapse(false);
			window.tinyMCE.activeEditor.execCommand('mceInsertContent', false, text);

			// Repaint to prevent shortcode_id bug.
			var content = window.tinyMCE.activeEditor.getContent();
			window.tinyMCE.activeEditor.setContent(content);
			window.tinyMCE.activeEditor.execCommand('mceRepaint');
		}
	}
}

const replaceShortcodeInEditor = function(editor, shortcode, text) {
	if (editor) {
		if (typeof window.tinyMCE !== 'undefined' && window.tinyMCE.get(editor) && !window.tinyMCE.get(editor).isHidden()) {
			window.tinyMCE.get(editor).focus(true);

			var content = window.tinyMCE.activeEditor.getContent();

            // Make sure we replace the shortcode we were editing
			var shortcode_id = 0;
			content = content.replace( /\[visual-link-preview ([^\]]*)\]/g, function( match ) {
				shortcode_id++;
				if(shortcode_id != shortcode) {
					return match;
				} else {
					return text;
				}
			});

			window.tinyMCE.activeEditor.setContent(content);
			window.tinyMCE.activeEditor.execCommand('mceRepaint');
		}
	}
}

const modal = ReactDOM.render(
	<Modal
		encodeLink={encodeLink}
		decodeLink={decodeLink}
		addTextToEditor={addTextToEditor}
		replaceShortcodeInEditor={replaceShortcodeInEditor}
	/>,
	document.getElementById( 'vlp-app' )
);

export { modal, decodeLink };