import { Component } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { AuthPageService } from './auth.service';
import { IAuth } from './auth.interface';

@Component({
  selector: 'page-auth',
  templateUrl: './auth.page.html',
  styleUrls: ['./auth.page.scss']
})
export class AuthPage {
  public formLogin = this.formBuilder.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', Validators.required],
  });

  constructor(
    private formBuilder: FormBuilder,
    private authPageService: AuthPageService
  ) { }

  ngOnInit(): void { }

  get email() { return this.formLogin.get('email') }
  get password() { return this.formLogin.get('password') }

  login = () => {
    let body: IAuth = {
      email: `${this.email}`,
      password: `${this.password}`
    }

    this.authPageService.login(body).then(response => console.log(response))
  }
}
