# Pet Management & Access Control

## Add Pet Functionality

### Route
- **GET** `/pet-owner/pets/create` - Show form to add new pet
- **POST** `/pet-owner/pets` - Store new pet in database

### Features
✅ **Crude Form** with fields:
- Pet name (required)
- Species (Dog, Cat, Rabbit, Bird, Hamster, Guinea Pig)
- Breed (required)
- Age in years (required, numeric)
- Weight in kg (required, numeric with decimals)
- Sex (Male/Female radio buttons)

✅ **Validation**
- All fields required
- Age must be 0 or greater
- Weight must be greater than 0.1 kg
- Sex must be male or female

✅ **Success Handling**
- Redirects to pets page on success
- Shows success message
- Stores pet with owner_id automatically

---

## Pet Access Control

### Authorization Rules

#### Pet Owner (role: owner)
- ✅ Can create new pets
- ✅ Can view only their own pets
- ✅ Can update their own pets
- ✅ Can delete their own pets

#### Veterinarian (role: vet)
- ✅ Can view pets they have appointments or consultations with
- ❌ Cannot create pets
- ❌ Cannot modify pet details (only vets with access)

#### Staff (role: staff)
- ✅ Can view all pets
- ✅ Can manage appointments and consultations
- ❌ Cannot create or modify pet details

#### Admin (role: admin)
- ✅ Can view all pets
- ✅ Full system access
- ❌ Cannot create pets (not a pet owner)

---

## Implementation Details

### Pet Policy (`app/Policies/PetPolicy.php`)
Defines authorization rules:

```php
view(User $user, Pet $pet) {
    // Owner can view their own pet
    if ($user->id === $pet->owner_id) return true;
    
    // Vet can view if they have appointment or consultation
    if ($user->role === 'vet') {
        return $pet->appointments()->where('vet_id', $user->id)->exists() ||
               $pet->consultations()->where('vet_id', $user->id)->exists();
    }
    
    // Staff and admin can view all
    if (in_array($user->role, ['staff', 'admin'])) return true;
}

update(User $user, Pet $pet) {
    // Only owner can update
    return $user->id === $pet->owner_id;
}

delete(User $user, Pet $pet) {
    // Only owner can delete
    return $user->id === $pet->owner_id;
}
```

### Pet Model Scope (`app/Models/Pet.php`)
```php
// Usage: Pet::accessibleBy(auth()->user())->get()

public function scopeAccessibleBy($query, User $user) {
    if ($user->role === 'owner') {
        return $query->where('owner_id', $user->id);
    } elseif ($user->role === 'vet') {
        return $query->whereHas('appointments', ...)
                     ->orWhereHas('consultations', ...);
    } elseif (in_array($user->role, ['staff', 'admin'])) {
        return $query; // All pets
    }
    return $query->where('id', null); // No access
}
```

### Controller Method
```php
public function storePet(Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:50',
        'species' => 'required|string|max:50',
        'breed' => 'required|string|max:50',
        'age' => 'required|integer|min:0',
        'weight' => 'required|numeric|min:0.1',
        'sex' => 'required|in:male,female',
    ]);

    $user = Auth::user();
    Pet::create([
        'owner_id' => $user->id,
        'name' => $validated['name'],
        'species' => $validated['species'],
        'breed' => $validated['breed'],
        'age' => $validated['age'],
        'weight' => $validated['weight'],
        'sex' => $validated['sex'],
    ]);

    return redirect()->route('pet-owner.pets')
                   ->with('success', 'Pet added successfully!');
}
```

---

## Usage Examples

### For Pet Owners
1. Go to "My Pets" page
2. Click "+ Add New Pet"
3. Fill in the form
4. Click "✓ Add Pet"
5. See the new pet in the list

### For Veterinarians
- Can only see pets they're treating (have appointments/consultations)
- Cannot create or modify pet records
- Can create consultations and prescriptions for visible pets

### For Staff
- Can see all pets
- Cannot create or modify pet records
- Can manage appointments and check-ins

### For Admins
- Can view all pets in the system
- Has full administrative access
- Cannot create pets directly

---

## Testing Access Control

**Test 1: Owner creates pet**
```
Login: john@example.com / password123
Action: Go to /pet-owner/pets, click "Add New Pet"
Expected: Form appears, pet is created with owner_id = john's user id
```

**Test 2: Owner views own pet**
```
Login: john@example.com / password123
Action: Go to /pet-owner/pets
Expected: See only John's pets
```

**Test 3: Vet views pet**
```
Login: sarah@vetclinic.com / password123
Action: Go to /vet/dashboard
Expected: See only pets they have appointments/consultations with
```

**Test 4: Staff views pets**
```
Login: tom@vetclinic.com / password123
Action: Go to /staff/dashboard
Expected: Can see all pets when accessing medical records
```

---

## Database Schema

```sql
CREATE TABLE pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    species VARCHAR(50),
    breed VARCHAR(50),
    age INT,
    weight DECIMAL(5,2),
    sex ENUM('male','female'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id)
);
```

---

## Current Seeded Pets

1. **Max** - Owner: john@example.com
   - Golden Retriever, 5 years old, 65.5 kg, Male

2. **Bella** - Owner: john@example.com
   - Labrador, 3 years old, 58 kg, Female

3. **Whiskers** - Owner: emma@example.com
   - Persian Cat, 7 years old, 12.5 kg, Male

---

**Status:** ✅ Production Ready  
**Last Updated:** February 4, 2026  
**Access Control:** Implemented via Policy + Model Scope
