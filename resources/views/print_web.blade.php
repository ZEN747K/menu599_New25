<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Order</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
                font-family: 'Courier New', monospace;
                font-size: 14px;
                line-height: 1.4;
            }
            .print-wrapper {
                width: 80mm;
                margin: 0 auto;
                padding: 5mm;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        
        .print-wrapper {
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .shop-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .order-info {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .order-info div {
            text-align: center;
            margin: 2px 0;
        }
        
        .order-item {
            margin-bottom: 10px;
            padding: 3px 0;
        }
        
        .item-name {
            font-weight: bold;
            text-align: center;
            font-size: 16px;
        }
        
        .item-option {
            font-size: 14px;
            margin-left: 10px;
            color: #666;
            text-align: center;
        }
        
        .item-remark {
            font-size: 14px;
            margin-left: 10px;
            color: #666;
            font-style: italic;
            text-align: center;
        }
        
        .quantity-price {
            text-align: center;
            margin-top: 3px;
            white-space: nowrap;
            font-size: 15px;
        }
        
        .total-section {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .total-line {
            text-align: center;
            margin: 5px 0;
            font-size: 16px;
            white-space: nowrap;
        }
        
        .total-line.main-total {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 12px;
        }
        
        /* สำหรับใบเสร็จ */
        .receipt-section {
            margin: 10px 0;
        }
        
        /* สำหรับออเดอร์ในครัว */
        .cook-header {
            background-color: #f0f0f0;
            padding: 5px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
        }
        
        .no-print {
            display: block;
            text-align: center;
            padding: 20px;
        }
        
        /* สำหรับตารางในออเดอร์ */
        .order-table {
            width: 100%;
            margin: 10px 0;
        }
        
        .order-table td {
            padding: 5px;
            vertical-align: top;
            font-size: 14px;
        }
        
        .order-table .item-col {
            text-align: left;
            width: 60%;
        }
        
        .order-table .qty-col {
            text-align: center;
            width: 15%;
        }
        
        .order-table .price-col {
            text-align: right;
            width: 25%;
            white-space: nowrap;
        }
        
        .order-table .total-row td {
            border-top: 2px solid #000;
            padding-top: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px;">พิมพ์</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; margin-left: 10px;">ปิด</button>
    </div>

    <div class="print-wrapper">
        <div id="print-content">
        </div>
    </div>

    <script>
        const jsonData = {!! $jsonData !!};
        const data = jsonData;
        
        function formatPrice(price) {
            return parseFloat(price).toFixed(2);
        }
        
        function formatDateTime(dateTime) {
            const date = new Date(dateTime);
            return date.toLocaleDateString('th-TH') + ' ' + date.toLocaleTimeString('th-TH');
        }
        
        function groupOrderItems(orderItems) {
            const grouped = {};
            
            orderItems.forEach(item => {
                let key = `${item.menu ? item.menu.name : 'เมนู'}_${item.price}`;
                
                if (item.option && item.option.length > 0) {
                    const optionKeys = item.option
                        .map(opt => opt.option ? opt.option.type : '')
                        .sort()
                        .join(',');
                    key += `_${optionKeys}`;
                }
                
                if (item.remark) {
                    key += `_${item.remark}`;
                }
                
                if (grouped[key]) {
                    grouped[key].quantity = parseInt(grouped[key].quantity) + parseInt(item.quantity);
                } else {
                    grouped[key] = {
                        ...item,
                        quantity: parseInt(item.quantity)
                    };
                }
            });
            
            return Object.values(grouped);
        }
        
        function renderContent() {
            const container = document.getElementById('print-content');
            let html = '';
            
            if (data.type === 'normal') {
                // ใบเสร็จธรรมดา
                html = renderNormalReceipt();
            } else if (data.type === 'taxfull') {
                // ใบกำกับภาษี
                html = renderTaxReceipt();
            } else if (data.type === 'order_admin') {
                // ปริ้นออเดอร์สำหรับแอดมิน
                html = renderOrderAdmin();
            } else if (data.type === 'order_cook') {
                // ปริ้นออเดอร์สำหรับครัว
                html = renderOrderCook();
            }
            
            container.innerHTML = html;
        }
        
        function renderNormalReceipt() {
            let html = `
                <div class="header">
                    <div class="shop-name">${data.config.name || 'ร้านอาหาร'}</div>
                    <div>ใบเสร็จรับเงิน</div>
                </div>
                
                <div class="order-info">
                    <div>เลขที่: ${data.pay.payment_number}</div>
                    <div>โต๊ะ: ${data.pay.table_id || '-'}</div>
                    <div>วันที่: ${formatDateTime(data.pay.created_at)}</div>
                </div>
                
                <div class="receipt-section">
            `;
            
            let total = 0;
            if (data.order && data.order.length > 0) {
                const groupedItems = groupOrderItems(data.order);
                groupedItems.forEach(item => {
                    const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
                    total += itemTotal;
                    
                    html += `
                        <div class="order-item">
                            <div class="item-name">${item.menu ? item.menu.name : 'เมนู'}</div>
                    `;
                    
                    if (item.option && item.option.length > 0) {
                        item.option.forEach(opt => {
                            if (opt.option) {
                                html += `<div class="item-option">+ ${opt.option.type}</div>`;
                            }
                        });
                    }
                    
                    if (item.remark) {
                        html += `<div class="item-remark">หมายเหตุ: ${item.remark}</div>`;
                    }
                    
                    html += `
                            <div class="quantity-price">
                                ${item.quantity} x ${formatPrice(item.price)} = ${formatPrice(itemTotal)}฿
                            </div>
                        </div>
                    `;
                });
            }
            
            html += `
                </div>
                
                <div class="total-section">
                    <div class="total-line main-total">
                        รวมทั้งสิ้น: ${formatPrice(data.pay.total)}฿
                    </div>
                </div>
                
                <div class="footer">
                    <div>ขอบคุณที่ใช้บริการ</div>
                </div>
            `;
            
            return html;
        }
        
        function renderTaxReceipt() {
            let html = `
                <div class="header">
                    <div class="shop-name">${data.config.name || 'ร้านอาหาร'}</div>
                    <div>ใบกำกับภาษี</div>
                </div>
                
                <div class="order-info">
                    <div>เลขที่: ${data.pay.payment_number}</div>
                    <div>โต๊ะ: ${data.pay.table_id || '-'}</div>
                    <div>วันที่: ${formatDateTime(data.pay.created_at)}</div>
            `;
            
            if (data.tax_full) {
                html += `
                    <div style="margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px;">
                        <div>ชื่อ: ${data.tax_full.name}</div>
                        <div>เบอร์โทร: ${data.tax_full.tel}</div>
                        <div>เลขภาษี: ${data.tax_full.tax_id}</div>
                        <div>ที่อยู่: ${data.tax_full.address}</div>
                    </div>
                `;
            }
            
            html += `</div><div class="receipt-section">`;
            
            let total = 0;
            if (data.order && data.order.length > 0) {
                const groupedItems = groupOrderItems(data.order);
                groupedItems.forEach(item => {
                    const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
                    total += itemTotal;
                    
                    html += `
                        <div class="order-item">
                            <div class="item-name">${item.menu ? item.menu.name : 'เมนู'}</div>
                    `;
                    
                    if (item.option && item.option.length > 0) {
                        item.option.forEach(opt => {
                            if (opt.option) {
                                html += `<div class="item-option">+ ${opt.option.type}</div>`;
                            }
                        });
                    }
                    
                    if (item.remark) {
                        html += `<div class="item-remark">หมายเหตุ: ${item.remark}</div>`;
                    }
                    
                    html += `
                            <div class="quantity-price">
                                ${item.quantity} x ${formatPrice(item.price)} = ${formatPrice(itemTotal)}฿
                            </div>
                        </div>
                    `;
                });
            }
            
            const vat = total * 0.07;
            const totalWithVat = total + vat;
            
            html += `
                </div>
                
                <div class="total-section">
                    <div class="total-line">
                        ยอดรวม: ${formatPrice(total)}฿
                    </div>
                    <div class="total-line">
                        ภาษีมูลค่าเพิ่ม 7%: ${formatPrice(vat)}฿
                    </div>
                    <div class="total-line main-total">
                        รวมทั้งสิ้น: ${formatPrice(totalWithVat)}฿
                    </div>
                </div>
                
                <div class="footer">
                    <div>ใบกำกับภาษีอย่างย่อ</div>
                    <div>ขอบคุณที่ใช้บริการ</div>
                </div>
            `;
            
            return html;
        }
        
        function renderOrderAdmin() {
            let html = `
                <div class="header">
                    <div class="shop-name">${data.config.name || 'ร้านค้าออนไลน์'}</div>
                    <div>เลขที่โต๊ะ: #${data.table ? data.table.table_number : data.table_id}</div>
                    <div>วันที่: ${formatDateTime(new Date())}</div>
                </div>
                
                <table class="order-table">
                    <tbody>
            `;
            
            if (data.order_details && data.order_details.length > 0) {
                const groupedItems = groupOrderItems(data.order_details);
                groupedItems.forEach(item => {
                    html += `
                        <tr>
                            <td class="item-col">
                                <div style="font-weight: bold;">${item.menu ? item.menu.name : 'เมนู'}</div>
                    `;
                    
                    if (item.option && item.option.length > 0) {
                        item.option.forEach(opt => {
                            if (opt.option) {
                                html += `<div style="font-size: 14px; color: #666;">+ ${opt.option.type}</div>`;
                            }
                        });
                    }
                    
                    if (item.remark) {
                        html += `<div style="font-size: 14px; color: #666;">หมายเหตุ: ${item.remark}</div>`;
                    }
                    
                    html += `
                            </td>
                            <td class="qty-col">${item.quantity}</td>
                            <td class="price-col">${formatPrice(item.price)}฿</td>
                        </tr>
                    `;
                });
            }
            
            // คำนวณราคารวม
            let total = 0;
            if (data.order_details && data.order_details.length > 0) {
                const groupedItems = groupOrderItems(data.order_details);
                total = groupedItems.reduce((sum, item) => {
                    return sum + (parseFloat(item.price) * parseInt(item.quantity));
                }, 0);
            }
            
            html += `
                        <tr class="total-row">
                            <td colspan="3">Total: ${formatPrice(total)}฿</td>
                        </tr>
                    </tbody>
                </table>
            `;
            
            return html;
        }
        
        function renderOrderCook() {
            let html = `
                <div class="header">
                    <div class="shop-name">${data.config.name || 'ร้านค้าออนไลน์'}</div>
                    <div>เลขที่โต๊ะ: #${data.table ? data.table.table_number : data.table_id}</div>
                    <div>วันที่: ${formatDateTime(new Date())}</div>
                </div>
                
                <table class="order-table">
                    <tbody>
            `;
            
            if (data.order_details && data.order_details.length > 0) {
                const groupedItems = groupOrderItems(data.order_details);
                groupedItems.forEach(item => {
                    html += `
                        <tr>
                            <td class="item-col">
                                <div style="font-weight: bold;">${item.menu ? item.menu.name : 'เมนู'}</div>
                    `;
                    
                    if (item.option && item.option.length > 0) {
                        item.option.forEach(opt => {
                            if (opt.option) {
                                html += `<div style="font-size: 14px; color: #666;">+ ${opt.option.type}</div>`;
                            }
                        });
                    }
                    
                    if (item.remark) {
                        html += `<div style="font-size: 14px; color: #666;">หมายเหตุ: ${item.remark}</div>`;
                    }
                    
                    html += `
                            </td>
                            <td class="qty-col">${item.quantity}</td>
                            <td class="price-col">${formatPrice(item.price)}฿</td>
                        </tr>
                    `;
                });
            }
            
            // คำนวณราคารวม
            let total = 0;
            if (data.order_details && data.order_details.length > 0) {
                const groupedItems = groupOrderItems(data.order_details);
                total = groupedItems.reduce((sum, item) => {
                    return sum + (parseFloat(item.price) * parseInt(item.quantity));
                }, 0);
            }
            
            html += `
                        <tr class="total-row">
                            <td colspan="3">Total: ${formatPrice(total)}฿</td>
                        </tr>
                    </tbody>
                </table>
            `;
            
            return html;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            renderContent();
            
            if (data.type === 'order_admin' || data.type === 'order_cook') {
                console.log('Auto print triggered for type:', data.type);
                setTimeout(function() {
                    console.log('Calling window.print()');
                    window.print();
                }, 1000);
            }
        });
    </script>
</body>
</html>