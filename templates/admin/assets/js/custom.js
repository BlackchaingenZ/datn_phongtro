function toSlug(title) {
    let slug = title.toLowerCase(); //Chuyển thành chữ thường

    slug = slug.trim(); //Xoá khoảng trắng 2 đầu

    //lọc dấu
    slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, "a");
    slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, "e");
    slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, "i");
    slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, "o");
    slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, "u");
    slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, "y");
    slug = slug.replace(/đ/gi, "d");

    //chuyển dấu cách (khoảng trắng) thành gạch ngang
    slug = slug.replace(/ /gi, "-");

    //Xoá tất cả các ký tự đặc biệt
    slug = slug.replace(
        /\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi,
        ""
    );

    return slug;
}

let sourceTitle = document.querySelector(".slug");
let slugRender = document.querySelector(".render-slug");

let renderLink = document.querySelector(".render-link");
if (renderLink != null) {
    // Lấy slug tự động
    let slug = "";
    if (slugRender !== null) {
        slug = "/" + slugRender.value.trim();
    }

    renderLink.querySelector("span").innerHTML = `<a href="${
        rootUrl + slug
    }" target="_blank">${rootUrl + slug}</a>`;
}

if (sourceTitle !== null && slugRender !== null) {
    sourceTitle.addEventListener("keyup", (e) => {
        if (!sessionStorage.getItem("save_slug")) {
            let title = e.target.value;

            if (title !== null) {
                let slug = toSlug(title);

                slugRender.value = slug;
            }
        }
    });

    sourceTitle.addEventListener("change", () => {
        sessionStorage.setItem("save_slug", 1);

        let currentLink =
            rootUrl + "/" + prefixUrl + "/" + slugRender.value.trim() + ".html";

        renderLink.querySelector("span a").innerHTML = currentLink;
        renderLink.querySelector("span a").href = currentLink;
    });

    slugRender.addEventListener("change", (e) => {
        let slugValue = e.target.value;
        if (slugValue.trim() == "") {
            sessionStorage.removeItem("save_slug");
            let slug = toSlug(sourceTitle.value);
            e.target.value = slug;
        }
        let currentLink =
            rootUrl + "/" + prefixUrl + "/" + slugRender.value.trim() + ".html";
        renderLink.querySelector("span a").innerHTML = currentLink;
        renderLink.querySelector("span a").href = currentLink;
    });

    if (slugRender.value.trim() == "") {
        sessionStorage.removeItem("save_slug");
    }
}

// Xử lý Ckeditor với class
let classTextarea = document.querySelectorAll(".editor");
if (classTextarea !== null) {
    classTextarea.forEach((item, index) => {
        item.id = "editor_" + (index + 1);
        CKEDITOR.replace(item.id);
    });
}
//Xử lý mở popup ckfinder

function openCkfinder() {
    
let chooseImages = document.querySelectorAll(".choose-image");
if (chooseImages !== null) {
    chooseImages.forEach(function (item) {
        item.addEventListener("click", function () {
            let parentElementObject = this.parentElement;
            while (parentElementObject) {
                parentElementObject = parentElementObject.parentElement;

                if (parentElementObject.classList.contains("ckfinder-group")) {
                    break;
                }
            }

            CKFinder.popup({
                chooseFiles: true,
                width: 800,
                height: 600,
                onInit: function (finder) {
                    finder.on("files:choose", function (evt) {
                        let fileUrl = evt.data.files.first().getUrl(); //Xử lý chèn link ảnh vào input

                        parentElementObject.querySelector(
                            ".image-render"
                        ).value = fileUrl;
                    });
                    finder.on("file:choose:resizedImage", function (evt) {
                        let fileUrl = evt.data.resizedUrl;
                        //Xử lý chèn link ảnh vào input
                        parentElementObject.querySelector(
                            ".image-render"
                        ).value = fileUrl;
                    });
                },
            });
        });
    });
}
}

openCkfinder();


// Xử lý thêm dữ liệu dưới dạng Repeater
let galleryItemHtml = `<div class="gallery-item">
<div class="row">        
    <div class="col-11">
        <div class="row ckfinder-group">
        <div class="col-8">
            <input type="text" name="gallery[]" id="name" class="form-control image-render" value="">   
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-success choose-image">Chọn ảnh</button>
        </div>
        </div>
    </div>
    <div class="col-1">
    <a href="#" class="remove btn btn-danger btn-block"><i class="fa fa-times"></i></a>
    </div>
</div>
</div>`

let addGalleryObject = document.querySelector(".add-gallery");
let galleryImageObject = document.querySelector(".gallery-images");

if (addGalleryObject !== null && galleryImageObject !== null) {
    addGalleryObject.addEventListener("click", function (e) {
        e.preventDefault();
        
        let galleryItemHtmlNode = new DOMParser().parseFromString(galleryItemHtml, 'text/html').querySelector('.gallery-item');
        galleryImageObject.appendChild(galleryItemHtmlNode);
        openCkfinder();

        // let galleryItemRemove = document.querySelectorAll(".gallery-images .remove");
        // if (galleryItemRemove !== null) {
        //     galleryItemRemove.forEach((removeItem) => {
        //         removeItem.addEventListener("click", function (e) {
        //             e.preventDefault()
        //             if (confirm('Bạn có chắc chắn muốn xóa ?')) {

        //                 let galleryItem = this;
        //                 while (galleryItem) {
        //                     galleryItem = galleryItem.parentElement;
            
        //                     if (galleryItem.classList.contains("gallery-item")) {
        //                         break;
        //                     }
        //                 }
        //                 if (galleryItem !==null) {
        //                     galleryItem.remove();
        //                 }
        //             }
        //         })
        //     })
        // }
    });
    galleryImageObject.addEventListener('click', (e) => {
        e.preventDefault();
        if(e.target.classList.contains('remove') || e.target.parentElement.classList.contains('remove')) {
           
            if (confirm('Bạn có chắc chắn muốn xóa ?')) {

                                let galleryItem = e.target;
                                while (galleryItem) {
                                    galleryItem = galleryItem.parentElement;
                    
                                    if (galleryItem.classList.contains("gallery-item")) {
                                        break;
                                    }
                                }
                                if (galleryItem !==null) {
                                    galleryItem.remove();
                                }
                            }
        }
    })
}
const slideItemHtml = `<div class="slide-item">
<div class="row">
    <div class="col-10">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label for="">Tiêu đề</label>
                    <input type="text" class="form-control" name="home_slide[slide_title][]">
                </div>
            </div>

            <div class="col-6">
                <div class="form-group">
                    <label for="">Nút xem thêm</label>
                    <input type="text" class="form-control" name="home_slide[slide_button_text][]">
                </div>
            </div>

            <div class="col-6">
                <div class="form-group">
                    <label for="">Link xem thêm</label>
                    <input type="text" class="form-control" name="home_slide[slide_button_link][]">
                </div>
            </div>

            <div class="col-6">
                <div class="form-group">
                    <label for="">Link video</label>
                    <input type="text" class="form-control" name="home_slide[slide_video][]">
                </div>
            </div>

            <div class="col-6">
                <div class="form-group">
                    <label for="name">Ảnh 1</label>
                    <div class="row ckfinder-group">
                        <div class="col-9">
                            <input type="text" name="home_slide[slide_image_1][]" id="name" class="form-control image-render" value="">   
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-success choose-image"><i class="fas fa-solid fa-upload"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="form-group">
                    <label for="name">Ảnh 2</label>
                    <div class="row ckfinder-group">
                        <div class="col-9">
                            <input type="text" name="home_slide[slide_image_2][]" id="name" class="form-control image-render" value="">   
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-success choose-image"><i class="fas fa-solid fa-upload"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="form-group">
                    <label for="name">Ảnh nền</label>
                    <div class="row ckfinder-group">
                        <div class="col-8">
                            <input type="text" name="home_slide[slide_bg][]" id="name" class="form-control image-render" value="">   
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-success choose-image"><i class="fas fa-solid fa-upload"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    

        <div class="form-group">
            <label for="">Mô tả</label>
            <textarea name="home_slide[slide_desc][]" class="form-control" ></textarea>
        </div>

        </div>
        
        <div class="col-2">
            <a href="" class="btn btn-danger btn-sm remove">&times;</a>
        </div>
</div>
</div>`

const addSlideObject = document.querySelector('.add-slide')
const slideWrapperObject = document.querySelector('.slide-wrapper')

if (addSlideObject !==null && slideWrapperObject !==null) {
    addSlideObject.addEventListener('click', function(){
        let sildeItemHtmlNode = new DOMParser().parseFromString(slideItemHtml, 'text/html').querySelector('.slide-item');
        slideWrapperObject.appendChild(sildeItemHtmlNode);
        openCkfinder();
    });

    slideWrapperObject.addEventListener('click', (e) => {
        e.preventDefault();
        if(e.target.classList.contains('remove') || e.target.parentElement.classList.contains('remove')) {
           
            if (confirm('Bạn có chắc chắn muốn xóa ?')) {

                                let slideItem = e.target;
                                while (slideItem) {
                                    slideItem = slideItem.parentElement;
                    
                                    if (slideItem.classList.contains("slide-item")) {
                                        break;
                                    }
                                }
                                if (slideItem !==null) {
                                    slideItem.remove();
                                }
                            }
        }
    })
}


const scoreInputs = document.querySelectorAll('.score');
const averageInput = document.getElementById('average');

scoreInputs.forEach(input => {
  input.addEventListener('input', calculateAverage);
});

function calculateAverage() {
  let totalScore = 0;
  let validInputs = 0;

  scoreInputs.forEach(input => {
    const score = parseFloat(input.value);
    if (!isNaN(score)) {
      totalScore += score;
      validInputs++;
    }
  });

  if (validInputs > 0) {
    const average = totalScore / validInputs;
    averageInput.value = average.toFixed(2);
  } else {
    averageInput.value = '';
  }
}
