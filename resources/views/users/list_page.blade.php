@extends('layouts.luxury-nav')

@section('title', 'หน้ารายละเอียด')

@section('content')
    <?php
    
    use App\Models\Config;
    
    $config = Config::first();
    ?>
    <style>
        .title-buy {
            font-size: 30px;
            font-weight: bold;
            color: <?=$config->color_font !='' ? $config->color_font : '#ffffff' ?>;
        }

        .title-list-buy {
            font-size: 25px;
            font-weight: bold;
        }

        .btn-plus {
            background: none;
            border: none;
            color: rgb(0, 156, 0);
            cursor: pointer;
            padding: 0;
            font-size: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-plus:hover {
            color: rgb(185, 185, 185);
        }

        .btn-delete {
            background: none;
            border: none;
            color: rgb(192, 0, 0);
            cursor: pointer;
            padding: 0;
            font-size: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            color: rgb(185, 185, 185);
        }

        .btn-aprove {
            background: linear-gradient(360deg, var(--primary-color), var(--sub-color));
            border-radius: 20px;
            border: 0px solid #0d9700;
            padding: 5px 0px;
            font-weight: bold;
            text-decoration: none;
            color: rgb(255, 255, 255);
            transition: background 0.3s ease;
        }

        .btn-aprove:hover {
            background: linear-gradient(360deg, var(--sub-color), var(--primary-color));
            cursor: pointer;
        }

        .btn-edit {
            background: transparent;
            /* ไม่มีพื้นหลัง */
            color: rgb(206, 0, 0);
            /* ตัวหนังสือสีแดง */
            border: none;
            /* ไม่มีเส้นขอบ */
            font-size: 12px;
            /* ขนาดตัวอักษร */
            text-decoration: underline;
            /* มีเส้นใต้ */
            padding: 0;
            /* เอา padding ออกเพื่อไม่ให้เกินขอบ */
            margin-top: -8px;
            cursor: pointer;
            /* เปลี่ยนเมาส์เป็น pointer */
        }
    </style>

    <div class="container">
        <div class="d-flex flex-column justify-content-center gap-2">
            <div class="title-buy">
                คำสั่งซื้อ
            </div>
            <div class="bg-white px-2 pt-3 shadow-lg d-flex flex-column aling-items-center justify-content-center"
                style="border-radius: 10px;">
                <div class="title-list-buy">
                    รายการอาหารที่สั่ง
                </div>
                <div id="order-summary" class="mt-2"></div>
                <div class="fw-bold fs-5 mt-5 " style="border-top:2px solid #7e7e7e; margin-bottom:-10px;">
                    ยอดชำระ
                </div>
                <div class="fw-bold text-center" style="font-size:45px; ">
                    <span id="total-price" style="color: #0d9700"></span><span class="text-dark ms-2">บาท</span>
                </div>
            </div>

            <a href="javascript:void(0);" class="btn-aprove mt-3" id="confirm-order-btn"
                style="display: none;">ยืนยันคำสั่งซื้อ</a>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
document.addEventListener("DOMContentLoaded", function() {
    const confirmButton = document.getElementById('confirm-order-btn');
    
    const urlParams = new URLSearchParams(window.location.search);
    const tableParam = urlParams.get('table');
    
    confirmButton.addEventListener('click', function(event) {
        event.preventDefault();

        const fileInput = document.getElementById('silp');
        const file = fileInput.files[0];
        const remarkInput = document.getElementById('remark');

        if (!file) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณาแนบสลิป',
                text: 'กรุณาเลือกไฟล์สลิปการโอนเงินก่อน'
            });
            return;
        }

        // ตรวจสอบประเภทไฟล์
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'ไฟล์ไม่ถูกต้อง',
                text: 'กรุณาเลือกไฟล์รูปภาพเท่านั้น (JPG, PNG)'
            });
            return;
        }

        // ตรวจสอบขนาดไฟล์ (5MB)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'ไฟล์ใหญ่เกินไป',
                text: 'ขนาดไฟล์ต้องไม่เกิน 5MB'
            });
            return;
        }

        Swal.fire({
            title: 'กำลังแนบสลิป...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('remark', remarkInput.value);
        formData.append('silp', file);
        
        if (tableParam) {
            formData.append('table_param', tableParam);
        }

        $.ajax({
            type: "POST",
            url: "{{ route('confirmPay') }}",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.close();
                if (response.status == true) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: response.message,
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMessage = 'เกิดข้อผิดพลาดในการแนบสลิป';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: errorMessage
                });
            }
        });
    });
});
</script>




@endsection
