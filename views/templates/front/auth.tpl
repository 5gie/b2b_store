{extends file="page.tpl"}

{block name='page_title'}
  {l s='Hurtownia B2B' d='Modules.B2BStore.Shop'}
{/block}
{block name='page_wrapper'}
    <section id="content" class="page-content page-stores">
        <div class="login_title">
            <div class="back-home">
                <div class="container">
                    <a href="/">
                        <svg class="cs-svg svg-long-arrow-left">
                            <use xlink:href="#svg-long-arrow-left"></use>
                        </svg>
                        Wróć o sklepu
                    </a>
                </div>
            </div>
            <h1>
                {l s='Hurtownia B2B' d='Shop.Istheme'}
            </h1>
        </div>
        {if $show_login_form}
        <div class="login_page login_page__login">
            <div class="container">
                <div class="card user-form">
                    <div class="row user-form__row">
                        {block name='login_form_container'}
                            <section class="col-md-6 col-12 user-form__block">
                                <div class="user-form__content card-body h-100 d-flex flex-column justify-content-between">
                                    <h4 class="h3">
                                        {l s='Zaloguj się do hurtowni' d='Shop.Istheme'}
                                    </h4>
                                    {render file='customer/_partials/login-form.tpl' ui=$login_form}

                                    <div class="forgot-password">
                                        <a href="{$urls.pages.password}" class="smallesttitle" rel="nofollow">
                                            <svg class="cs-svg svg-sync">
                                                <use xlink:href="#svg-sync"></use>
                                            </svg>
                                            {l s='Nie pamiętam hasła' d='Shop.Theme.Customeraccount'}
                                        </a>
                                    </div>

                                </div>
                            </section>
                        {/block}

                        <div class="user-form__block  col-md-6 col-12">
                            <div class="user-form__content card-body h-100 d-flex flex-column justify-content-between">
                                <h4 class="h3">
                                    {l s='Brak dostępu lub problem z logowaniem?' d='Shop.Istheme'}
                                </h4>

                                <p>
                                    {l s='Jeżeli Twoja Firma nie ma jeszcze dostępu do E-HURTOWNI, załóż konto, zaloguj się i przejdź do formularza dostępu do strefy B2B.' d='Shop.Istheme'}
                                    <br>
                                    <br>
                                    {l s='Lub zadzwoń i zapytaj o szczegóły pod numerem:' d='Shop.Istheme'}
                                    <a href="tel:{$shop.phone}">
                                        <span class="d-flex h4 align-items-center">
                                        <svg class="mr-2" width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M21.4531 1.73828L17.4141 0.835938C16.4688 0.621094 15.5234 1.09375 15.1797 1.95312L13.2891 6.33594C12.9453 7.10938 13.1602 8.05469 13.8477 8.61328L15.5664 10.0312C14.3633 12.2656 12.4727 14.1133 10.2383 15.3594L8.82031 13.6406C8.26172 12.9531 7.31641 12.7383 6.54297 13.082L2.16016 14.9297C1.30078 15.3164 0.828125 16.2617 1.04297 17.207L1.94531 21.2461C2.16016 22.1484 2.97656 22.75 3.87891 22.75C14.4062 22.75 23 14.2422 23 3.67188C23 2.76953 22.3555 1.95312 21.4531 1.73828ZM3.96484 20.6875L3.0625 16.8203L7.27344 15.0156L9.67969 17.9375C13.9336 15.918 16.125 13.7266 18.1445 9.47266L15.2227 7.06641L17.0273 2.85547L20.9375 3.75781C20.8945 13.082 13.2891 20.6445 3.96484 20.6875Z" fill="black"/>
                                        </svg>
                                        {$shop.phone}
                                        </span>
                                    </a>
                                </p>

                                <div class="">
                                    <a href="{$register_url}" class="continue btn btn-full btn-full-2">
                                        {l s='Rejestracja B2B' d='Shop.Istheme'}
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {else}
        <div class="login_page login_page__register">
			<div class="container">
				<div class="card user-form">
					<div class="card-body">

						{* ONEALL Social Login *}
						{* {$hook_create_account_top nofilter} *}

						<section class="register-form">
                            {render file='customer/_partials/customer-form.tpl' ui=$register_form}

							<div class="forgot-password">
								<a href="{$login_url}" class="">
									{l s='Log in instead!' d='Shop.Theme.Customeraccount'}
								</a>
							</div>
						</section>
					</div>
					{* <div class="card-footer text-center"> *}
					{* </div> *}
				</div>
			</div>
		</div>
        {/if}
    </section>
{/block}