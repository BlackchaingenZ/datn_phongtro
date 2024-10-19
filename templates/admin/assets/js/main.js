// Toast
let toast = document.querySelector('#MessageFlash');
toast.style.opacity = 'block';
setTimeout(function() {
    toast.style.display = 'none';
}, 3000);

// Thêm bằng modal
var modal = document.getElementById("myModal");
var btn = document.getElementById("openModalBtn");
var span = document.getElementsByClassName("close")[0];

btn.onclick = function() {
  modal.style.display = "block";
}

span.onclick = function() {
  modal.style.display = "none";
}

// window.onclick = function(event) {
//   if (event.target == modal) {
//     modal.style.display = "none";
//   }
// }

function formatCurrency(input) {
  // Loại bỏ tất cả các ký tự không phải số
  let value = input.value.replace(/[^0-9]/g, '');
  
  // Chuyển đổi chuỗi thành số nguyên rồi định dạng lại thành chuỗi có dấu phẩy
  value = Number(value).toLocaleString('en');

  // Cập nhật giá trị ô input
  input.value = value;
}