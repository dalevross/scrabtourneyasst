var editor = new TINY.editor.edit('editor', {
		id: 'tinyeditor',
		width: 320,
		height: 250,
		cssclass: 'tinyeditor',
		controlclass: 'tinyeditor-control',
		rowclass: 'tinyeditor-header',
		dividerclass: 'tinyeditor-divider',
		controls: ['bold', 'italic', 'underline', 'strikethrough', '|',
			'|', 'outdent', 'indent', '|','undo', 'redo','n'
			, 'size', '|', 'image', 'link', 'unlink', '|', 'print'],
		footer: false,
		xhtml: true,
		bodyid: 'editor'		
	});
