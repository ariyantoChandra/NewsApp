import { Component, OnInit } from '@angular/core';
import { HttpService } from '../http-service';
import { Router } from '@angular/router';
import { ToastController } from '@ionic/angular';

@Component({
  selector: 'app-register-writer',
  templateUrl: './register-writer.page.html',
  styleUrls: ['./register-writer.page.scss'],
  standalone: false,
})
export class RegisterWriterPage implements OnInit {
  countries: any[] = [];
  confirmPassword: string = '';
  form = {
    username: '',
    password: '',
    fullname: '',
    email: '',
    biography: '',
    media_id: 1,
    profile_picture_address: 'default.png',
  };

  mediaList: any[] = [];

  constructor(
    private httpService: HttpService,
    private router: Router,
    private toastCtrl: ToastController
  ) {}

  ngOnInit() {
    this.loadMedias();
  }

  loadMedias() {
    this.httpService.getMedias().subscribe((res: any) => {
      if (res.status === 'success') {
        this.mediaList = res.data;
      }
    });
  }

  onRegister() {
    if (!this.form.username || !this.form.password || !this.form.media_id) {
      this.showToast('Mohon lengkapi data');
      return;
    }

    this.httpService.register_writer(this.form).subscribe(
      (res: any) => {
        if (res.status === 'success') {
          this.showToast('Registrasi Berhasil! Silakan Login.');
          this.router.navigate(['/login']);
        } else {
          this.showToast('Gagal: ' + res.message);
        }
      },
      (err) => {
        this.showToast('Terjadi kesalahan koneksi');
      }
    );
  }

  async showToast(msg: string) {
    const toast = await this.toastCtrl.create({
      message: msg,
      duration: 2000,
    });
    toast.present();
  }
}
