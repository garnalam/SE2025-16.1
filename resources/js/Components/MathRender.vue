<script setup>
import { computed } from 'vue';
import { marked } from 'marked';
import katex from 'katex';
// CSS đã import ở app.js nên không cần import lại ở đây, 
// nhưng để chắc chắn component độc lập thì giữ lại cũng không sao.
import 'katex/dist/katex.min.css';

const props = defineProps({
    content: { type: String, default: '' },
});

const renderedContent = computed(() => {
    if (!props.content) return '';
    let text = props.content;
    const mathBlocks = [];

    // BƯỚC 1: Tìm và "giấu" các công thức toán đi (thay bằng placeholder)
    // Để tránh việc markedjs hiểu nhầm các ký tự như _, * trong công thức là markdown
    
    // Giấu công thức Block $$...$$
    text = text.replace(/\$\$([\s\S]*?)\$\$/g, (match, formula) => {
        mathBlocks.push({ type: 'block', formula });
        return `%%%MATH_BLOCK_${mathBlocks.length - 1}%%%`;
    });

    // Giấu công thức Inline $...$
    text = text.replace(/\$([^\$]*?)\$/g, (match, formula) => {
        mathBlocks.push({ type: 'inline', formula });
        return `%%%MATH_INLINE_${mathBlocks.length - 1}%%%`;
    });

    // BƯỚC 2: Parse Markdown (Lúc này text không còn chứa ký tự toán học gây lỗi)
    let html = marked.parse(text);

    // BƯỚC 3: Khôi phục và Render công thức KaTeX vào lại HTML
    mathBlocks.forEach((item, index) => {
        const placeholder = item.type === 'block' 
            ? `%%%MATH_BLOCK_${index}%%%` 
            : `%%%MATH_INLINE_${index}%%%`;
        
        let rendered = '';
        try {
            rendered = katex.renderToString(item.formula, {
                displayMode: item.type === 'block',
                throwOnError: false, // Quan trọng: Không crash nếu công thức sai
                errorColor: '#cc0000',
                output: 'html'
            });
        } catch (e) {
            rendered = `<span style="color:red">Lỗi công thức: ${item.formula}</span>`;
        }
        
        // Thay thế placeholder bằng HTML của KaTeX
        html = html.replace(placeholder, rendered);
    });

    return html;
});
</script>

<template>
    <div class="prose prose-invert max-w-none text-slate-300 text-sm leading-relaxed" v-html="renderedContent"></div>
</template>