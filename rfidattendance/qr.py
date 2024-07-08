import qrcode

def create_qr_code(data, file_path):
    """
    Create a QR code and save it as an image file.

    :param data: The data to encode in the QR code (e.g., a URL).
    :param file_path: The file path where the QR code image will be saved.
    """
    # Create a QR code instance
    qr = qrcode.QRCode(
        version=1,  # controls the size of the QR Code, higher number means bigger size
        error_correction=qrcode.constants.ERROR_CORRECT_L,  # controls the error correction used for the QR Code
        box_size=10,  # controls how many pixels each “box” of the QR code is
        border=4,  # controls how many boxes thick the border should be (the default is 4)
    )

    # Add data to the QR code
    qr.add_data(data)
    qr.make(fit=True)

    # Create an image from the QR Code instance
    img = qr.make_image(fill_color="black", back_color="white")

    # Save the image
    img.save(file_path)
    print(f"QR code generated and saved as {file_path}")

# Example usage
data = "https://www.example.com"
file_path = "qrcode.png"
create_qr_code(data, file_path)
