import { Component } from '@angular/core';
import { AuthPageService } from './auth.service';

@Component({
  selector: 'page-auth',
  templateUrl: './auth.page.html',
  styleUrls: ['./auth.page.scss']
})
export class AuthPage {
  constructor(
    private authPageService: AuthPageService
  ) { }
}
