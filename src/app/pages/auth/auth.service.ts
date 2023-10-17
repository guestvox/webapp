import { HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { HttpService } from '@services/http.service';
import { IAuth } from './auth.interface';

@Injectable({
  providedIn: 'root'
})
export class AuthPageService {
  constructor(
    private httpService: HttpService
  ) { }

  login = (body: IAuth) => {
    return this.httpService.post(`/v1/login`, body, true)
      .then(response => response)
      .catch(error => console.error(error))
  }
}
