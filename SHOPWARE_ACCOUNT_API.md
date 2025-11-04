# Shopware Account API Documentation

> Comprehensive documentation for the Shopware Account API integration in shopware-cli

## Table of Contents

- [Overview](#overview)
- [Authentication](#authentication)
- [API Client Architecture](#api-client-architecture)
- [Core APIs](#core-apis)
  - [Profile API](#profile-api)
  - [Membership/Company Management](#membershipcompany-management)
  - [Producer API](#producer-api)
  - [Extension Management](#extension-management)
  - [Extension Binary Management](#extension-binary-management)
  - [Extension Media Management](#extension-media-management)
  - [Code Review API](#code-review-api)
  - [Merchant Shop API](#merchant-shop-api)
  - [Update Compatibility API](#update-compatibility-api)
- [Data Structures Reference](#data-structures-reference)
- [Usage Examples](#usage-examples)

---

## Overview

The Shopware Account API provides programmatic access to manage:
- User accounts and profiles
- Company memberships and roles
- Extension development and publishing
- Shop management and composer tokens
- Extension compatibility checking

**Base URL:** `https://api.shopware.com`

**Implementation Location:** `internal/account-api/`

---

## Authentication

### Token-Based Authentication

The API uses JWT token-based authentication with automatic caching.

#### Login Flow

```go
import accountApi "github.com/shopware/shopware-cli/internal/account-api"

client, err := accountApi.NewApi(ctx, accountApi.LoginRequest{
    Email:    "user@example.com",
    Password: "password",
})
```

**Implementation:** `internal/account-api/login.go:21-96`

**Process:**
1. Checks for cached token in `~/.cache/shopware-cli/shopware-api-client-token.json`
2. If no valid cache, performs login via `POST /accesstokens`
3. Fetches user memberships
4. Caches authenticated client to disk

#### Token Structure

```go
type token struct {
    Token         string      // JWT token
    Expire        tokenExpire // Expiration info
    UserAccountID int         // User account ID
    UserID        int         // Company ID (active membership)
    LegacyLogin   bool
}
```

**Location:** `internal/account-api/login.go:134-146`

#### Authentication Methods

| Method | Description | Location |
|--------|-------------|----------|
| `isTokenValid()` | Checks if token expires in >60 seconds | `client.go:83-96` |
| `InvalidateTokenCache()` | Deletes cached token (logout) | `client.go:168-179` |
| `NewAuthenticatedRequest()` | Creates HTTP request with auth headers | `client.go:28-41` |

#### Required Headers

```
x-shopware-token: {JWT_TOKEN}
content-type: application/json
accept: application/json
user-agent: shopware-cli/{version}
```

---

## API Client Architecture

### Client Structure

```go
type Client struct {
    Token            token        // Authentication token
    ActiveMembership Membership   // Currently active company membership
    Memberships      []Membership // All available memberships
}
```

**Location:** `internal/account-api/client.go:22-26`

### Core Client Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `GetActiveCompanyID()` | `int` | Returns active company ID |
| `GetUserID()` | `int` | Returns user account ID |
| `GetActiveMembership()` | `Membership` | Returns active membership |
| `GetMemberships()` | `[]Membership` | Returns all memberships |

**Location:** `internal/account-api/client.go:67-81`

---

## Core APIs

### Profile API

Get information about the authenticated user.

#### Get User Profile

```go
profile, err := client.GetMyProfile(ctx)
```

**Endpoint:** `GET /account/{userAccountId}`
**Location:** `internal/account-api/profile.go:13-47`

**Response Structure:**

```go
type MyProfile struct {
    Id           int
    Email        string
    CreationDate string
    Banned       bool
    Verified     bool
    PersonalData struct {
        Id         int
        Salutation struct { Id int; Name string; Description string }
        FirstName  string
        LastName   string
        Locale     struct { Id int; Name string; Description string }
    }
    PartnerMarketingOptIn bool
    SelectedMembership    Membership
}
```

**Location:** `internal/account-api/profile.go:49-112`

---

### Membership/Company Management

Manage company memberships and switch between companies.

#### Membership Structure

```go
type Membership struct {
    Id           int
    CreationDate string
    Active       bool
    Member       struct {
        Id           int
        Email        string
        AvatarUrl    interface{}
        PersonalData struct {
            Id         int
            Salutation struct { Id int; Name string; Description string }
            FirstName  string
            LastName   string
            Locale     struct { Id int; Name string; Description string }
        }
    }
    Company struct {
        Id             int
        Name           string
        CustomerNumber string
    }
    Roles []struct {
        Id           int
        Name         string
        CreationDate string
        Company      interface{}
        Permissions  []struct {
            Id      int
            Context string
            Name    string
        }
    }
}
```

**Location:** `internal/account-api/login.go:161-201`

#### API Methods

##### Fetch Memberships

```go
memberships := client.GetMemberships()
```

**Internal Endpoint:** `GET /account/{userAccountId}/memberships`
**Location:** `internal/account-api/login.go:98-132`

##### Change Active Membership

```go
err := client.ChangeActiveMembership(ctx, selectedMembership)
```

**Endpoint:** `POST /account/{userAccountId}/memberships/change`
**Location:** `internal/account-api/login.go:219-256`

**Note:** Updates token cache after successful change

##### Get Roles

```go
roles := membership.GetRoles() // Returns []string
```

**Location:** `internal/account-api/login.go:203-211`

---

### Producer API

Access and manage extension producer (vendor) information.

#### Get Producer Endpoint

```go
producer, err := client.Producer(ctx)
```

**Endpoint:** `GET /companies/{companyId}/allocations`
**Location:** `internal/account-api/producer.go:24-45`

Validates that the company is unlocked as a producer and returns a `ProducerEndpoint` for further operations.

#### Get Producer Profile

```go
profile, err := producer.Profile(ctx)
```

**Endpoint:** `GET /producers?companyId={companyId}`
**Location:** `internal/account-api/producer.go:56-77`

**Producer Structure:**

```go
type Producer struct {
    Id                        int
    Prefix                    string
    Name                      string
    Website                   string
    IconPath                  string
    IconIsSet                 bool
    ShopwareID                string
    UserId                    int
    CompanyId                 int
    CompanyName               string
    SaleMail                  string
    SupportMail               string
    RatingMail                string
    SupportedLanguages        []Locale
    IconURL                   string
    HasSupportInfoActivated   bool
    IsPremiumExtensionPartner bool
    // ... additional fields
}
```

**Location:** `internal/account-api/producer.go:79-116`

---

### Extension Management

Manage extensions (plugins/apps) for a producer.

#### List Extensions

```go
extensions, err := producer.Extensions(ctx, &accountApi.ListExtensionCriteria{
    Limit:         50,
    Offset:        0,
    OrderBy:       "name",
    OrderSequence: "asc",
    Search:        "search-term",
})
```

**Endpoint:** `GET /plugins?producerId={id}&...`
**Location:** `internal/account-api/producer.go:126-151`

**Criteria Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| `Limit` | `int` | Page size limit |
| `Offset` | `int` | Page offset |
| `OrderBy` | `string` | Sort field |
| `OrderSequence` | `string` | Sort direction (`asc`/`desc`) |
| `Search` | `string` | Search query |

**Location:** `internal/account-api/producer.go:118-124`

#### Get Extension by Name

```go
extension, err := producer.GetExtensionByName(ctx, "ExtensionName")
```

**Location:** `internal/account-api/producer.go:153-170`

#### Get Extension by ID

```go
extension, err := producer.GetExtensionById(ctx, extensionId)
```

**Endpoint:** `GET /plugins/{id}`
**Location:** `internal/account-api/producer.go:172-192`

#### Update Extension

```go
err := producer.UpdateExtension(ctx, extension)
```

**Endpoint:** `PUT /plugins/{id}`
**Location:** `internal/account-api/producer.go:378-393`

#### Extension Structure

```go
type Extension struct {
    Id              int
    Producer        Producer
    Type            struct { Id int; Name string; Description string }
    Name            string
    Code            string
    ModuleKey       string
    LifecycleStatus struct { Id int; Name string; Description string }
    Generation      struct { Id int; Name string; Description string }
    ActivationStatus struct { Id int; Name string; Description string }
    ApprovalStatus   struct { Id int; Name string; Description string }
    StandardLocale   Locale
    License          struct { Id int; Name string; Description string }

    Infos []struct {
        Id                 int
        Locale             Locale
        Name               string
        Description        string
        InstallationManual string
        ShortDescription   string
        Highlights         string
        Features           string
        MetaTitle          string
        MetaDescription    string
        Tags               []StoreTag
        Videos             []StoreVideo
        Faqs               []StoreFaq
        SupportInfo        interface{}
    }

    PriceModels         []interface{}
    Variants            []interface{}
    StoreAvailabilities []StoreAvailablity
    Categories          []StoreCategory
    Category            *StoreCategory
    Addons              []struct { /* Addon details */ }

    // Feature Flags
    LastChange                          string
    CreationDate                        string
    Support                             bool
    SupportOnlyCommercial               bool
    IconPath                            string
    IconIsSet                           bool
    ExamplePageUrl                      string
    MigrationSupport                    bool
    AutomaticBugfixVersionCompatibility bool
    HiddenInStore                       bool
    IsPremiumPlugin                     bool
    IsAdvancedFeature                   bool
    // ... many more flags
}
```

**Location:** `internal/account-api/producer.go:194-368`

#### Get Shopware Versions

```go
versions, err := producer.GetSoftwareVersions(ctx, "classic") // or "next"
```

**Endpoint:** `GET /pluginstatics/softwareVersions?filter=[...]`
**Location:** `internal/account-api/producer.go:395-415`

**Response:**

```go
type SoftwareVersion struct {
    Id          int
    Name        string     // e.g., "6.5.0"
    Parent      interface{}
    Selectable  bool
    Major       string
    ReleaseDate string
    Status      string
}
```

**Helper Methods:**
- `FilterOnVersion(constraint)` - Filter versions by version constraint
- `FilterOnVersionStringList(constraint)` - Get version name strings

**Location:** `internal/account-api/producer.go:432-472`

#### Get Extension General Info

```go
info, err := producer.GetExtensionGeneralInfo(ctx)
```

**Endpoint:** `GET /pluginstatics/all`
**Location:** `internal/account-api/producer.go:516-535`

Returns categories, generations, statuses, locales, licenses, and other metadata.

---

### Extension Binary Management

Manage extension binary versions and files.

#### Get Extension Binaries

```go
binaries, err := producer.GetExtensionBinaries(ctx, extensionId)
```

**Endpoint:** `GET /producers/{producerId}/plugins/{extensionId}/binaries`
**Location:** `internal/account-api/producer_extension.go:70-89`

**ExtensionBinary Structure:**

```go
type ExtensionBinary struct {
    Id                          int
    Name                        string
    Version                     string
    Status                      struct { Id int; Name string; Description string }
    CompatibleSoftwareVersions  SoftwareVersionList
    Changelogs []struct {
        Id     int
        Locale Locale
        Text   string
    }
    CreationDate                string
    LastChangeDate              string
    IonCubeEncrypted            bool
    LicenseCheckRequired        bool
    HasActiveCodeReviewWarnings bool
}
```

**Location:** `internal/account-api/producer_extension.go:26-49`

#### Create Extension Binary

```go
binary, err := producer.CreateExtensionBinary(ctx, extensionId, accountApi.ExtensionCreate{
    Version:          "1.0.0",
    SoftwareVersions: []string{"6.5.0", "6.5.1"},
    Changelogs: []accountApi.ExtensionUpdateChangelog{
        {Locale: "en_GB", Text: "Initial release"},
        {Locale: "de_DE", Text: "Erstveröffentlichung"},
    },
})
```

**Endpoint:** `POST /producers/{producerId}/plugins/{extensionId}/binaries`
**Location:** `internal/account-api/producer_extension.go:109-133`

#### Update Binary Info

```go
err := producer.UpdateExtensionBinaryInfo(ctx, extensionId, accountApi.ExtensionUpdate{
    Id:                   binaryId,
    SoftwareVersions:     []string{"6.5.0", "6.5.1"},
    IonCubeEncrypted:     false,
    LicenseCheckRequired: false,
    Changelogs: []accountApi.ExtensionUpdateChangelog{
        {Locale: "en_GB", Text: "Bug fixes"},
    },
})
```

**Endpoint:** `PUT /producers/{producerId}/plugins/{extensionId}/binaries/{binaryId}`
**Location:** `internal/account-api/producer_extension.go:91-107`

#### Upload Binary File

```go
err := producer.UpdateExtensionBinaryFile(ctx, extensionId, binaryId, "/path/to/extension.zip")
```

**Endpoint:** `POST /producers/{producerId}/plugins/{extensionId}/binaries/{binaryId}/file`
**Location:** `internal/account-api/producer_extension.go:135-170`

Uploads ZIP file via `multipart/form-data` with field name `file`.

---

### Extension Media Management

Manage extension icons and image galleries.

#### Update Extension Icon

```go
err := producer.UpdateExtensionIcon(ctx, extensionId, "/path/to/icon.png")
```

**Endpoint:** `POST /plugins/{extensionId}/icon`
**Location:** `internal/account-api/producer_extension.go:172-232`

**Features:**
- Automatically resizes to 256x256 if needed
- Converts to PNG format
- Uploads via `multipart/form-data`

#### Get Extension Images

```go
images, err := producer.GetExtensionImages(ctx, extensionId)
```

**Endpoint:** `GET /plugins/{extensionId}/pictures`
**Location:** `internal/account-api/producer_extension.go:247-266`

**ExtensionImage Structure:**

```go
type ExtensionImage struct {
    Id         int
    RemoteLink string
    Details    []struct {
        Id        int
        Preview   bool
        Activated bool
        Caption   string
        Locale    Locale
    }
    Priority int
}
```

**Location:** `internal/account-api/producer_extension.go:234-245`

#### Add Extension Image

```go
image, err := producer.AddExtensionImage(ctx, extensionId, "/path/to/image.png")
```

**Endpoint:** `POST /plugins/{extensionId}/pictures`
**Location:** `internal/account-api/producer_extension.go:299-342`

#### Update Extension Image

```go
err := producer.UpdateExtensionImage(ctx, extensionId, image)
```

**Endpoint:** `PUT /plugins/{extensionId}/pictures/{imageId}`
**Location:** `internal/account-api/producer_extension.go:281-297`

#### Delete Extension Image

```go
err := producer.DeleteExtensionImages(ctx, extensionId, imageId)
```

**Endpoint:** `DELETE /plugins/{extensionId}/pictures/{imageId}`
**Location:** `internal/account-api/producer_extension.go:268-279`

---

### Code Review API

Trigger and monitor automated code reviews for extension binaries.

#### Trigger Code Review

```go
err := producer.TriggerCodeReview(ctx, extensionId)
```

**Endpoint:** `POST /plugins/{extensionId}/reviews`
**Location:** `internal/account-api/producer_extension.go:344-355`

#### Get Review Results

```go
results, err := producer.GetBinaryReviewResults(ctx, extensionId, binaryId)
```

**Endpoint:** `GET /plugins/{extensionId}/binaries/{binaryId}/checkresults`
**Location:** `internal/account-api/producer_extension.go:357-376`

**BinaryReviewResult Structure:**

```go
type BinaryReviewResult struct {
    Id       int
    BinaryId int
    Type     struct { Id int; Name string; Description string }
    Message  string
    CreationDate string
    SubCheckResults []struct {
        SubCheck    string
        Status      string
        Passed      bool
        Message     string
        HasWarnings bool
    }
}
```

**Location:** `internal/account-api/producer_extension.go:378-395`

#### Review Helper Methods

```go
result.HasPassed()    // bool - Type ID 3 or name "automaticcodereviewsucceeded"
result.HasWarnings()  // bool - Any sub-check has warnings
result.IsPending()    // bool - Type ID 4
result.GetSummary()   // string - Sanitized HTML summary of failed checks
```

**Location:** `internal/account-api/producer_extension.go:397-430`

---

### Merchant Shop API

Manage merchant shops and composer authentication tokens.

#### Get Merchant Endpoint

```go
merchant := client.Merchant()
```

**Location:** `internal/account-api/merchant.go:13-15`

#### List Shops

```go
shops, err := merchant.Shops(ctx)
```

**Endpoint:** `GET /shops?limit=100&userId={companyId}`
**Location:** `internal/account-api/merchant.go:17-34`

**MerchantShop Structure:**

```go
type MerchantShop struct {
    Id                  int
    Domain              string
    Type                string
    CompanyId           int
    CompanyName         string
    Dispo               int
    Balance             float64
    IsPartnerShop       bool
    Subaccount          *int
    IsCommercial        bool
    DocumentComment     string
    Activated           bool
    AccountId           string
    ShopNumber          string
    CreationDate        string
    Branch              interface{}
    SubscriptionModules []struct { /* Module subscriptions */ }
    Environment         struct { Id int; Name string; Description string }
    Staging             bool
    Instance            bool
    Mandant             bool
    ShopwareVersion     struct {
        Id          int
        Name        string
        Parent      int
        Selectable  bool
        Major       string
        ReleaseDate string
        Public      bool
    }
    ShopwareEdition                string
    DomainIdn                      string
    LatestVerificationStatusChange struct { /* Status info */ }
}
```

**Location:** `internal/account-api/merchant.go:36-131`

**Helper Method:**

```go
shop := shops.GetByDomain("example.com")
```

**Location:** `internal/account-api/merchant.go:133-141`

#### Get Composer Token

```go
token, err := merchant.GetComposerToken(ctx, shopId)
```

**Endpoint:** `GET /companies/{companyId}/shops/{shopId}/packagestoken`
**Location:** `internal/account-api/merchant.go:143-168`

#### Generate Composer Token

```go
token, err := merchant.GenerateComposerToken(ctx, shopId)
```

**Endpoint:** `POST /companies/{companyId}/shops/{shopId}/packagestoken`
**Location:** `internal/account-api/merchant.go:170-190`

#### Save Composer Token

```go
err := merchant.SaveComposerToken(ctx, shopId, token)
```

**Endpoint:** `POST /companies/{companyId}/shops/{shopId}/packagestoken/{token}`
**Location:** `internal/account-api/merchant.go:192-201`

---

### Update Compatibility API

Check extension compatibility with future Shopware versions.

#### Check Extension Updates

```go
import accountApi "github.com/shopware/shopware-cli/internal/account-api"

compatibility, err := accountApi.GetFutureExtensionUpdates(
    ctx,
    "6.5.0",      // Current Shopware version
    "6.6.0",      // Future Shopware version
    []accountApi.UpdateCheckExtension{
        {Name: "SwagExample", Version: "1.0.0"},
        {Name: "SwagAnother", Version: "2.3.1"},
    },
)
```

**Endpoint:** `POST /swplatform/autoupdate?language=en-GB&shopwareVersion={currentVersion}`
**Location:** `internal/account-api/updates.go:32-74`

**Note:** This is a public endpoint (no authentication required)

**Request Structure:**

```go
type UpdateCheckExtension struct {
    Name    string
    Version string
}
```

**Location:** `internal/account-api/updates.go:14-17`

**Response Structure:**

```go
type UpdateCheckExtensionCompatibility struct {
    Name     string
    Label    string
    IconPath string
    Status   UpdateCheckExtensionCompatibilityStatus
}

type UpdateCheckExtensionCompatibilityStatus struct {
    Name  string
    Label string
    Type  string // "success", "warning", "error"
}
```

**Location:** `internal/account-api/updates.go:19-30`

---

## Data Structures Reference

### Common Types

#### Locale

```go
type Locale struct {
    Id   int
    Name string // e.g., "de_DE", "en_GB"
}
```

#### Store Types

```go
type StoreCategory struct {
    Id          int
    Name        string
    Description string
    Parent      interface{}
    Position    int
    Public      bool
    Visible     bool
    Suggested   bool
    Applicable  bool
    Details     interface{}
    Active      bool
}

type StoreTag struct {
    Name string
}

type StoreVideo struct {
    URL string
}

type StoreFaq struct {
    Question string
    Answer   string
    Position int
}
```

**Location:** `internal/account-api/producer.go:427-470`

---

## Usage Examples

### Complete Extension Upload Workflow

```go
package main

import (
    "context"
    accountApi "github.com/shopware/shopware-cli/internal/account-api"
)

func main() {
    ctx := context.Background()

    // 1. Login
    client, err := accountApi.NewApi(ctx, accountApi.LoginRequest{
        Email:    "developer@example.com",
        Password: "password",
    })
    if err != nil {
        panic(err)
    }

    // 2. Get producer endpoint
    producer, err := client.Producer(ctx)
    if err != nil {
        panic(err)
    }

    // 3. Get extension by name
    extension, err := producer.GetExtensionByName(ctx, "SwagExample")
    if err != nil {
        panic(err)
    }

    // 4. Get Shopware versions
    versions, err := producer.GetSoftwareVersions(ctx, "classic")
    if err != nil {
        panic(err)
    }

    // 5. Create new binary version
    binary, err := producer.CreateExtensionBinary(ctx, extension.Id, accountApi.ExtensionCreate{
        Version:          "1.2.0",
        SoftwareVersions: []string{"6.5.0", "6.5.1"},
        Changelogs: []accountApi.ExtensionUpdateChangelog{
            {Locale: "en_GB", Text: "Added new features"},
            {Locale: "de_DE", Text: "Neue Funktionen hinzugefügt"},
        },
    })
    if err != nil {
        panic(err)
    }

    // 6. Upload ZIP file
    err = producer.UpdateExtensionBinaryFile(ctx, extension.Id, binary.Id, "/path/to/plugin.zip")
    if err != nil {
        panic(err)
    }

    // 7. Trigger code review
    err = producer.TriggerCodeReview(ctx, extension.Id)
    if err != nil {
        panic(err)
    }

    // 8. Wait and check review results
    results, err := producer.GetBinaryReviewResults(ctx, extension.Id, binary.Id)
    if err != nil {
        panic(err)
    }

    for _, result := range results {
        if result.HasPassed() {
            println("Code review passed!")
        } else if result.IsPending() {
            println("Code review pending...")
        } else {
            println("Code review failed:", result.GetSummary())
        }
    }
}
```

### Company Switching Example

```go
// List all memberships
for i, membership := range client.GetMemberships() {
    fmt.Printf("%d: %s (Company ID: %d)\n",
        i,
        membership.Company.Name,
        membership.Company.Id,
    )
}

// Switch to a different company
var selectedMembership accountApi.Membership
for _, m := range client.GetMemberships() {
    if m.Company.Id == desiredCompanyId {
        selectedMembership = m
        break
    }
}

err := client.ChangeActiveMembership(ctx, selectedMembership)
if err != nil {
    panic(err)
}

// Invalidate cache to force new token on next API call
err = accountApi.InvalidateTokenCache()
```

### Merchant Shop Management Example

```go
// List all shops
shops, err := client.Merchant().Shops(ctx)
if err != nil {
    panic(err)
}

for _, shop := range shops {
    fmt.Printf("Shop: %s (%s) - Version: %s\n",
        shop.Domain,
        shop.Environment.Name,
        shop.ShopwareVersion.Name,
    )
}

// Get composer token for a shop
shop := shops.GetByDomain("example.com")
if shop != nil {
    token, err := client.Merchant().GetComposerToken(ctx, shop.Id)
    if err != nil {
        // Token doesn't exist, generate new one
        token, err = client.Merchant().GenerateComposerToken(ctx, shop.Id)
        if err != nil {
            panic(err)
        }
    }

    fmt.Printf("Composer token: %s\n", token)
}
```

### Extension Image Management Example

```go
producer, err := client.Producer(ctx)
if err != nil {
    panic(err)
}

extension, err := producer.GetExtensionByName(ctx, "SwagExample")
if err != nil {
    panic(err)
}

// Update icon
err = producer.UpdateExtensionIcon(ctx, extension.Id, "/path/to/icon.png")
if err != nil {
    panic(err)
}

// Add gallery images
image1, err := producer.AddExtensionImage(ctx, extension.Id, "/path/to/screenshot1.png")
if err != nil {
    panic(err)
}

image2, err := producer.AddExtensionImage(ctx, extension.Id, "/path/to/screenshot2.png")
if err != nil {
    panic(err)
}

// Update image details
image1.Details[0].Caption = "Main interface"
image1.Details[0].Preview = true
err = producer.UpdateExtensionImage(ctx, extension.Id, image1)
if err != nil {
    panic(err)
}
```

### Update Compatibility Check Example

```go
import accountApi "github.com/shopware/shopware-cli/internal/account-api"

// Check if extensions are compatible with future version
compatibility, err := accountApi.GetFutureExtensionUpdates(
    ctx,
    "6.5.0",  // Current version
    "6.6.0",  // Target version
    []accountApi.UpdateCheckExtension{
        {Name: "SwagExample", Version: "1.0.0"},
        {Name: "SwagPayment", Version: "2.1.3"},
    },
)

if err != nil {
    panic(err)
}

for _, ext := range compatibility {
    switch ext.Status.Type {
    case "success":
        fmt.Printf("✓ %s is compatible\n", ext.Name)
    case "warning":
        fmt.Printf("⚠ %s: %s\n", ext.Name, ext.Status.Label)
    case "error":
        fmt.Printf("✗ %s is incompatible: %s\n", ext.Name, ext.Status.Label)
    }
}
```

---

## API Method Summary

### Authentication & User Management (5 methods)
- `NewApi()` - Login and create authenticated client
- `GetMyProfile()` - Get user profile
- `ChangeActiveMembership()` - Switch company context
- `InvalidateTokenCache()` - Logout
- `GetMemberships()` - List all memberships

### Producer Management (3 methods)
- `Producer()` - Get producer endpoint
- `Profile()` - Get producer profile
- `GetExtensionGeneralInfo()` - Get metadata (categories, locales, etc.)

### Extension Management (5 methods)
- `Extensions()` - List extensions with filtering
- `GetExtensionByName()` - Find extension by name
- `GetExtensionById()` - Get extension by ID
- `UpdateExtension()` - Update extension metadata
- `GetSoftwareVersions()` - Get compatible Shopware versions

### Extension Binary Management (4 methods)
- `GetExtensionBinaries()` - List binary versions
- `CreateExtensionBinary()` - Create new version
- `UpdateExtensionBinaryInfo()` - Update version metadata
- `UpdateExtensionBinaryFile()` - Upload ZIP file

### Extension Media Management (5 methods)
- `UpdateExtensionIcon()` - Upload/update icon
- `GetExtensionImages()` - List gallery images
- `AddExtensionImage()` - Add gallery image
- `UpdateExtensionImage()` - Update image metadata
- `DeleteExtensionImages()` - Remove image

### Code Review (2 methods)
- `TriggerCodeReview()` - Start automated review
- `GetBinaryReviewResults()` - Get review status/results

### Merchant Shop Management (4 methods)
- `Shops()` - List merchant shops
- `GetComposerToken()` - Get existing composer token
- `GenerateComposerToken()` - Generate new composer token
- `SaveComposerToken()` - Save composer token

### Update Compatibility (1 method)
- `GetFutureExtensionUpdates()` - Check extension compatibility (public API)

**Total: 29 public API methods**

---

## Error Handling

### Standard Error Patterns

All API methods return Go errors that can be checked and handled:

```go
result, err := client.SomeMethod(ctx, params)
if err != nil {
    // Handle error
    log.Printf("API error: %v", err)
    return err
}
```

### HTTP Error Responses

- Status codes >= 400 return error with response body
- Implementation: `internal/account-api/client.go:60-62`

### Context-Based Logging

```go
import "github.com/shopware/shopware-cli/logging"

logging.FromContext(ctx).Debugf("Debug message: %v", data)
logging.FromContext(ctx).Infof("Info message")
logging.FromContext(ctx).Errorf("Error occurred: %v", err)
```

---

## Implementation Files

| File | Purpose | Location |
|------|---------|----------|
| `client.go` | Base client and authentication | `internal/account-api/` |
| `login.go` | Authentication and membership management | `internal/account-api/` |
| `profile.go` | User profile operations | `internal/account-api/` |
| `producer.go` | Producer and extension management | `internal/account-api/` |
| `producer_extension.go` | Extension binary and media operations | `internal/account-api/` |
| `merchant.go` | Merchant shop and composer token management | `internal/account-api/` |
| `updates.go` | Extension compatibility checking | `internal/account-api/` |

---

## CLI Commands Using This API

| Command | Description | Implementation |
|---------|-------------|----------------|
| `account login` | Authenticate with Shopware Account | `cmd/account/account_login.go` |
| `account logout` | Invalidate authentication | `cmd/account/account_logout.go` |
| `account company list` | List memberships | `cmd/account/account_company_list.go` |
| `account company use` | Switch company | `cmd/account/account_company_use.go` |
| `account producer extension list` | List extensions | `cmd/account/account_producer_extension_list.go` |
| `account producer extension upload` | Upload extension binary | `cmd/account/account_producer_extension_upload.go` |
| `account producer extension info` | Show extension details | `cmd/account/account_producer_extension_info.go` |
| `account merchant shop list` | List merchant shops | `cmd/account/account_merchant_shop_list.go` |

---

## Additional Resources

- **Configuration**: `.shopware-cli.yaml` - Global CLI configuration
- **Token Cache**: `~/.cache/shopware-cli/shopware-api-client-token.json`
- **Dependencies**:
  - `github.com/Masterminds/semver/v3` - Version constraint handling
  - `go.uber.org/zap` - Structured logging

---

*Generated from shopware-cli source code analysis*
*Last updated: 2025-11-04*
