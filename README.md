# imagecompress
大图片压缩，支持格式GIF、JPG、PNG、BMP，将图片大小控制在2M以内

主要用到了PHP imagejpeg方法，
图片大小小于2M，不压缩
图片大小超过2M，按照阶梯压缩
