import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AuthPageRoutingModule } from './auth-routing.module';
import { AuthPage } from './auth.page';

@NgModule({
  declarations: [
    AuthPage
  ],
  imports: [
    CommonModule,
    AuthPageRoutingModule
  ]
})
export class AuthPageModule { }
