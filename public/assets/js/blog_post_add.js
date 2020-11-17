ClassicEditor
    .create( document.querySelector( "#content" ), {
        toolbar: [ "heading", "|", "bold", "italic", "link", "bulletedList", "numberedList", "fontColor", "|", "indent", "outdent", "alignment", "|", "blockQuote", "insertTable", "code", "codeBlock", "|", "undo", "redo" ],
        heading: {
            options: [
                { model: "paragraph", title: "Paragraph", class: "ck-heading_paragraph" },
                { model: "heading1", view: "h2", title: "Heading 1", class: "ck-heading_heading1" },
                { model: "heading2", view: "h3", title: "Heading 2", class: "ck-heading_heading2" },
                { model: "heading3", view: "h4", title: "Heading 3", class: "ck-heading_heading3" },
                { model: "heading4", view: "h5", title: "Heading 4", class: "ck-heading_heading4" }
            ]
        },
        fontColor: {
            colors: [
                { color: "#e9490f", label: "Orange" }
            ]
        }
    });