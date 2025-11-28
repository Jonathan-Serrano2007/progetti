from socket import *
serverPort = 12000
serverSocket = socket(AF_INET, SOCK_DGRAM) #ipv4, udp
serverSocket.bind(('', serverPort))
print("Server ready to receive")
while True:
    message, clientAdress = serverSocket.recvfrom(2048)
    print("Client adress: ", clientAdress)
    modifiedMessage = message.decode().upper()
    serverSocket.sendto(modifiedMessage.encode(), clientAdress)