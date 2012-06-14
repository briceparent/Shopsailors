var diaporamaInserterDialog = {
	init : function(ed) {
		tinyMCEPopup.resizeToInnerSize();
	},

	insert : function(name,x,y) {

        var ed = tinyMCEPopup.editor, dom = ed.dom;
        
        tinyMCE.execCommand('mceInsertContent',false,'<RENDER_DIAPORAMA name="'+ name +'" id="diaporama_'+ name +'" nodisplay="content_editor"><div style="width:'+ x +'px;height:'+ y +'px;background:transparent url(/templates/global/admin/diapo_'+ x +'x'+ y +'.png) no-repeat center top;">'+ name +'</div></RENDER_DIAPORAMA>');

		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(diaporamaInserterDialog.init, diaporamaInserterDialog);
