// 图片上传功能
function initImageUpload() {
    const uploadArea = document.querySelector('.image-upload-area');
    const fileInput = document.querySelector('.image-upload-input');
    const preview = document.querySelector('.image-upload-preview');
    const removeBtn = document.querySelector('.image-upload-remove');
    const qualitySlider = document.querySelector('.image-quality-slider');
    const qualityValue = document.querySelector('.image-quality-value');

    // 拖放功能
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFileUpload(fileInput.files[0]);
        }
    });

    // 点击上传
    uploadArea.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            handleFileUpload(fileInput.files[0]);
        }
    });

    // 移除图片
    removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        resetUploadArea();
    });

    // 质量滑块
    qualitySlider.addEventListener('input', () => {
        qualityValue.textContent = qualitySlider.value;
    });

    function handleFileUpload(file) {
        if (!file.type.match('image.*')) {
            alert('请选择图片文件');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.classList.add('show');
            uploadArea.classList.add('has-image');
            removeBtn.classList.add('show');
        };
        reader.readAsDataURL(file);
    }

    function resetUploadArea() {
        fileInput.value = '';
        preview.src = '';
        preview.classList.remove('show');
        uploadArea.classList.remove('has-image');
        removeBtn.classList.remove('show');
    }
}

// 初始化所有功能
document.addEventListener('DOMContentLoaded', function() {
    initImageUpload();
});