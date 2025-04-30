<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use DanHarrin\LivewireRateLimiter\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class Register extends BaseRegister
{
    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Step 1 - User Type Selection
                Step::make('User Type')
                    ->schema([
                        Radio::make('user_type')
                            ->label('I am a:')
                            ->options([
                                'faculty' => 'Faculty',
                                'director' => 'Director',
                                'student' => 'Student',
                            ])
                            ->required()
                        // ->live()
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                // Step 2 - Conditional Fields
                Step::make('Account Details')
                    ->schema([
                        // Common fields for all user types
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: User::class),

                        // Student-specific fields (conditional)
                        TextInput::make('student_id')
                            ->label('Student ID')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn(callable $get): bool => $get('user_type') === 'student'),

                        Select::make('course')
                            ->label('Course')
                            ->options([
                                'computer_science' => 'Computer Science',
                                'engineering' => 'Engineering',
                                'business' => 'Business',
                                // Add other courses as needed
                            ])
                            ->required()
                            ->visible(fn(callable $get): bool => $get('user_type') === 'student'),

                        // Password fields
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required()
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->same('passwordConfirmation')
                            ->validationAttribute('password'),

                        TextInput::make('passwordConfirmation')
                            ->label('Confirm password')
                            ->password()
                            ->required()
                            ->dehydrated(false),
                    ])
                    ->columns(1)
                    ->statePath('data')
                    ->visible(fn(callable $get): bool => filled($get('user_type')))
                    ->columnSpanFull(),
            ]);
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (\DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException $exception) {
            $this->addError('email', __('filament-panels::pages/auth/register.messages.throttled', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => ceil($exception->secondsUntilAvailable / 60),
            ]));

            return null;
        }

        $data = $this->form->getState();

        // Create the user with appropriate role
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'user_type' => $data['user_type'],
            // Add student-specific fields if applicable
            'student_id' => $data['user_type'] === 'student' ? $data['student_id'] : null,
            'course' => $data['user_type'] === 'student' ? $data['course'] : null,
        ]);

        event(new Registered($user));

        Filament::auth()->login($user);

        session()->regenerate();

        return app(RegistrationResponse::class);
    }
}
