import { Component, OnInit } from '@angular/core';
import { HttpService } from '../http-service';
import { ToastController } from '@ionic/angular';

@Component({
  selector: 'app-create-category',
  templateUrl: './create-category.page.html',
  styleUrls: ['./create-category.page.scss'],
  standalone: false,
})
export class CreateCategoryPage implements OnInit {
  categoryName: string = '';

  constructor(private http: HttpService, private toastCtrl: ToastController) {}

  ngOnInit() {}

  async saveCategory() {
    if (!this.categoryName.trim()) {
      this.showToast('Nama kategori tidak boleh kosong', 'warning');
      return;
    }

    const data = { name: this.categoryName };

    this.http.create_category(data).subscribe(
      (res: any) => {
        if (res.status === 'success') {
          this.showToast('Kategori berhasil dibuat!', 'success');
          this.categoryName = ''; 
        } else {
          this.showToast('Gagal: ' + res.message, 'danger');
        }
      },
      (err) => {
        console.error(err);
        this.showToast('Terjadi kesalahan koneksi', 'danger');
      }
    );
  }

  async showToast(msg: string, color: string) {
    const toast = await this.toastCtrl.create({
      message: msg,
      duration: 2000,
      color: color,
      position: 'bottom',
    });
    toast.present();
  }
}
