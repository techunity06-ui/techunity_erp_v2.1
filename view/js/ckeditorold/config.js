/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	
	// %REMOVE_START%
	// The configuration options below are needed when running CKEditor from source files.
	config.plugins = 'basicstyles,blockquote,toolbar,clipboard,panelbutton,colorbutton,colordialog,copyformatting,resize,elementspath,enterkey,entities,popup,filebrowser,find,floatingspace,listblock,richcombo,font,format,horizontalrule,htmlwriter,wysiwygarea,image,indent,indentblock,indentlist,justify,menubutton,language,link,list,liststyle,magicline,maximize,pagebreak,removeformat,save,selectall,showblocks,showborders,sourcearea,specialchar,scayt,stylescombo,tab,table,tabletools,tableselection,undo,lineutils,uploadwidget,strinsert';
	config.skin = 'moono-lisa';
	config.allowedContent = true;
	config.autoParagraph=false;
	config.basicEntities = false;
	config.height = '115px';
	config.extraPlugins = 'strinsert';


	// %REMOVE_END%	//notificationaggregator,dialogui,dialog,about,a11yhelp,dialogadvtab,bidi,div,smiley,widgetselection,widget,filetoolsfakeobjects,flash,,notification,button,pastetext,pastefromword,preview,print,newpage,wsc,,panel,floatpanel,menu,contextmenu,forms,templates,iframe,
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';


};
