import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { HttpService } from '../http-service';

@Component({
  selector: 'app-register',
  templateUrl: './register.page.html',
  styleUrls: ['./register.page.scss'],
  standalone: false,
})
export class RegisterPage implements OnInit {
  countries: any[] = [];
  selectedPhonePrefix: string = '';
  confirmPassword: string = '';

  regData = {
    username: '',
    password: '',
    fullname: '',
    email: '',
    birthdate: '',
    phone_number: '',
    gender: '',
    biography: '',
    profile_picture_address: 'default.png',
    country_id: 1,
  };

  constructor(private http: HttpService, private router: Router) {}
  ngOnInit() {
    this.loadCountries();
  }

  loadCountries() {
    this.http.get_countries().subscribe(
      (res: any) => {
        if (res.status === 'success') {
          this.countries = res.data;
          this.updatePrefix();
        }
      },
      (err) => {console.error(err);
      }
    );
  }

  onCountryChange(event: any) {
    this.regData.country_id = event.detail.value;
    this.updatePrefix();
  }

  updatePrefix() {
    const selectedCountry = this.countries.find(
      (c) => c.id == this.regData.country_id
    );
    if (selectedCountry) {
      this.selectedPhonePrefix = selectedCountry.telephone;
    } else {
      this.selectedPhonePrefix = '';
    }
  }

  onRegister() {
    if (!this.regData.fullname) {
      alert('Nama Lengkap (Fullname) harus diisi!');
      return;
    }
    if (/\d/.test(this.regData.fullname)) {
      alert('Nama Lengkap tidak boleh mengandung angka!');
      return;
    }
    if (!this.regData.username || !this.regData.password) {
      alert('Username dan Password wajib diisi!');
      return;
    }
    if (this.regData.password.length < 8) {
      alert('Password minimal harus 8 karakter!');
      return;
    }
    if (this.regData.password !== this.confirmPassword) {
      alert('Konfirmasi Password tidak cocok dengan Password!');
      return;
    }

    this.http.register_user(this.regData).subscribe(
      (res: any) => {
        if (res.status === 'success') {
          alert('Register Berhasil! Silahkan Login.');
          this.router.navigate(['/login']);
        } else {
          alert('Gagal: ' + res.message);
        }
      },
      (err) => {
        console.error(err);
        alert('Terjadi kesalahan koneksi.');
      }
    );
  }
  formatDate(event: any) {
    const value = event.detail.value;
    if (value) {
      this.regData.birthdate = Array.isArray(value)
        ? value[0]
        : value.split('T')[0];
    }
  }
}
