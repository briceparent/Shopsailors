var bgcChangerDialog = {
	init : function(ed) {
		tinyMCEPopup.resizeToInnerSize();
	},

	insert : function(color) {

        var ed = tinyMCEPopup.editor, dom = ed.dom;
        
        tinyMCE.activeEditor.getBody().style.backgroundColor = color;

		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(bgcChangerDialog.init, bgcChangerDialog);
