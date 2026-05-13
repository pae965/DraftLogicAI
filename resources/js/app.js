import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Auto-init Tiptap editors
import { initSectionEditor } from './editor/tiptap-init.js';
window.initSectionEditor = initSectionEditor;
