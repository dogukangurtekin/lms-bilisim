import './bootstrap';
import axios from 'axios';
import { Editor, Extension } from '@tiptap/core';
import { StarterKit } from '@tiptap/starter-kit';
import { TextStyle } from '@tiptap/extension-text-style';
import { Highlight } from '@tiptap/extension-highlight';
import { TextAlign } from '@tiptap/extension-text-align';
import { Placeholder } from '@tiptap/extension-placeholder';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const RichTextStyle = TextStyle.extend({
    addOptions() {
        return {
            types: ['textStyle'],
        };
    },
    addGlobalAttributes() {
        return [{
            types: this.options.types,
            attributes: {
                fontFamily: {
                    default: null,
                    parseHTML: element => element.style.fontFamily || null,
                    renderHTML: attributes => {
                        if (!attributes.fontFamily) return {};
                        return { style: `font-family: ${attributes.fontFamily}` };
                    },
                },
                fontSize: {
                    default: null,
                    parseHTML: (element) => element.style.fontSize || null,
                    renderHTML: (attributes) => {
                        if (!attributes.fontSize) return {};
                        return { style: `font-size: ${attributes.fontSize}` };
                    },
                },
                color: {
                    default: null,
                    parseHTML: element => element.style.color || null,
                    renderHTML: attributes => {
                        if (!attributes.color) return {};
                        return { style: `color: ${attributes.color}` };
                    },
                },
            },
        }];
    },
    addCommands() {
        return {
            setFontFamily: (fontFamily) => ({ chain }) => chain().setMark('textStyle', { fontFamily }).run(),
            setFontSize: (fontSize) => ({ chain }) => chain().setMark('textStyle', { fontSize }).run(),
            setColor: (color) => ({ chain }) => chain().setMark('textStyle', { color }).run(),
        };
    },
});

window.__TiptapBundle = {
    Editor,
    Extension,
    StarterKit,
    TextStyle: RichTextStyle,
    Highlight,
    TextAlign,
    Placeholder,
};
