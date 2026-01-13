import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { RegisterWriterPageRoutingModule } from './register-writer-routing.module';

import { RegisterWriterPage } from './register-writer.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    RegisterWriterPageRoutingModule
  ],
  declarations: [RegisterWriterPage]
})
export class RegisterWriterPageModule {}
