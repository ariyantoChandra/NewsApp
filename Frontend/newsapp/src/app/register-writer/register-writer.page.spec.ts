import { ComponentFixture, TestBed } from '@angular/core/testing';
import { RegisterWriterPage } from './register-writer.page';

describe('RegisterWriterPage', () => {
  let component: RegisterWriterPage;
  let fixture: ComponentFixture<RegisterWriterPage>;

  beforeEach(() => {
    fixture = TestBed.createComponent(RegisterWriterPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
