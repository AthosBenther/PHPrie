# PHPrie

A small set of php CLI tools to deal with BeamNG.drive's messy world editor since I couldn't be bored with implementing them into [BRIE](https://github.com/AthosBenther/BRIE) right now...

## Requirements
- PHP ^8.4
- Composer ^2.0

## Usage
`$ php PHPrie.php <controller> [--p=<param1,param2,...>] [--fn=<function>] [--fnp=<arg1,arg2,...>]`

| Argument       | Required | Type                 | Description                                                 |
| -------------- | -------- | -------------------- | ----------------------------------------------------------- |
| `<controller>` | ✅        | string               | The controller name or route identifier to be executed.     |
| `--p`          | ❌        | comma-separated list | Optional parameters passed to the controller (as an array). |
| `--fn`         | ❌        | string               | The function name to be called inside the controller.       |
| `--fnp`        | ❌        | comma-separated list | Arguments passed to the specified function (as an array).   |
