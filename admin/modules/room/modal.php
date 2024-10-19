   <!-- Modal -->
   <div id="myModall" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <form method="post" class="row" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="">Tên phòng <span style="color: red">*</span></label>
                                <input type="text" placeholder="Tên phòng" name="tenphong" id="" class="form-control" value="<?php echo old('tenphong', $old); ?>">
                                <?php echo form_error('tenphong', $errors, '<span class="error">', '</span>'); ?>
                            </div>

                            <div class="form-group">
                                <label for="">Diện tích</label>
                                <input type="text" placeholder="Diện tích (m2)" name="dientich" id="" class="form-control" value="<?php echo old('dientich', $old); ?>">
                                <?php echo form_error('dientich', $errors, '<span class="error">', '</span>'); ?>
                            </div>

                            <div class="form-group">
                                <label for="">Giá thuê <span style="color: red">*</span></label>
                                <input type="text" placeholder="Giá thuê (đ)" name="giathue" id="" class="form-control" value="<?php echo old('giathue', $old); ?>">
                                <?php echo form_error('giathue', $errors, '<span class="error">', '</span>'); ?>
                            </div>

                            <div class="form-group">
                                <label for="">Giá tiền cọc <span style="color: red">*</span></label>
                                <input type="text" placeholder="Giá cọc (đ)" name="tiencoc" id="" class="form-control" value="<?php echo old('tiencoc', $old); ?>">
                                <?php echo form_error('tiencoc', $errors, '<span class="error">', '</span>'); ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="">Ngày lập hóa đơn</label>
                                <select name="ngaylaphd" id="" class="form-select">
                                    <option value="">Chọn ngày</option>
                                    <?php
                                        for($i=1; $i <= 31; $i++) { ?>
                                            <option value="<?php echo $i; ?>">Ngày <?php echo $i; ?></option>
                                        <?php } 
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="">Chu kỳ thu tiền</label>
                                <select name="chuky" id="" class="form-select">
                                    <option value="">Chọn chu kỳ</option>
                                    <?php 
                                        for($i = 1; $i < 7; $i+=2) { ?>
                                            <option value="<?php echo $i; ?>"> <?php echo $i;?> tháng</option>
                                        <?php }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="">Ngày vào ở</label>
                                <input type="date" name="ngayvao" id="" class="form-control" value="<?php echo old('ngayvao', $old); ?>">
                                <?php echo form_error('ngayvao', $errors, '<span class="error">', '</span>'); ?>
                            </div>

                            <div class="form-group">
                                <label for="">Thời hạn hợp đồng</label>
                                <input type="date" name="ngayra" id="" class="form-control" value="<?php echo old('ngayra', $old); ?>">
                                <?php echo form_error('ngayra', $errors, '<span class="error">', '</span>'); ?>
                            </div>
                        
                        </div>                  
                        <div class="from-group">                    
                                <div class="btn-row">
                                    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Thêm phòng</button>
                                    <a style="margin-left: 20px " href="<?php echo getLinkAdmin('room', 'lists') ?>" class="btn btn-success"><i class="fa fa-forward"></i></a>
                                </div>
                        </div>
                </form>
            </div>
        </div>